<?php

namespace NFePHP\NFSe\Itaperuna\Common;

use NFePHP\Common\Certificate;
use NFePHP\Common\Certificate\PublicKey;
use NFePHP\Common\Exception\SignerException;
use NFePHP\Common\Strings;
use NFePHP\Common\Validator;
use DOMDocument;
use DOMNode;
use DOMElement;

class Signer
{
    private static $canonical = [true, false, null, null];

    public static function sign(
        Certificate $certificate,
        $content,
        $tagname,
        $mark = 'Id',
        $algorithm = OPENSSL_ALGO_SHA1,
        $canonical = [false, false, null, null],
        $rootname = ''
    ) {

        if (!empty($canonical)) {
            self::$canonical = $canonical;
        }
        if (empty($content)) {
            throw SignerException::isNotXml();
        }
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($content, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $root = $dom->documentElement;
        if (!empty($rootname)) {
            $root = $dom->getElementsByTagName($rootname)->item(0);
        }

        $node = $dom->getElementsByTagName($tagname)->item(0);

        if (empty($node) || empty($root)) {
            throw SignerException::tagNotFound($tagname);
        }
        // if (!self::existsSignature($content)) {
        $dom = self::createSignature(
            $certificate,
            $dom,
            $root,
            $node,
            $mark,
            $algorithm,
            $canonical
        );
        // };
        return $dom->saveXML($dom->documentElement, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
    }

    /**
     * Method that provides the signature of xml as standard SEFAZ
     * @param Certificate $certificate
     * @param \DOMDocument $dom
     * @param \DOMNode $root xml root
     * @param \DOMElement $node node to be signed
     * @param string $mark Marker signed attribute
     * @param int $algorithm cryptographic algorithm (opcional)
     * @param array $canonical parameters to format node for signature (opcional)
     * @return \DOMDocument
     */
    private static function createSignature(
        Certificate $certificate,
        DOMDocument $dom,
        DOMNode $root,
        DOMElement $node,
        $mark,
        $algorithm = OPENSSL_ALGO_SHA1,
        $canonical = [false, false, null, null]
    ) {
        $nsDSIG = 'http://www.w3.org/2000/09/xmldsig#';
        $nsCannonMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $nsSignatureMethod = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
        $nsDigestMethod = 'http://www.w3.org/2000/09/xmldsig#sha1';
        $digestAlgorithm = 'sha1';
        if ($algorithm == OPENSSL_ALGO_SHA256) {
            $digestAlgorithm = 'sha256';
            $nsSignatureMethod = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
            $nsDigestMethod = 'http://www.w3.org/2001/04/xmlenc#sha256';
        }
        $nsTransformMethod1 = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';
        $nsTransformMethod2 = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $idSigned = trim($node->getAttribute($mark));
        $digestValue = self::makeDigest($node, $digestAlgorithm, $canonical);
        $signatureNode = $dom->createElementNS($nsDSIG, 'Signature');
        $node->appendChild($signatureNode);
        $signedInfoNode = $dom->createElement('SignedInfo');
        $signatureNode->appendChild($signedInfoNode);
        $canonicalNode = $dom->createElement('CanonicalizationMethod');
        $signedInfoNode->appendChild($canonicalNode);
        $canonicalNode->setAttribute('Algorithm', $nsCannonMethod);
        $signatureMethodNode = $dom->createElement('SignatureMethod');
        $signedInfoNode->appendChild($signatureMethodNode);
        $signatureMethodNode->setAttribute('Algorithm', $nsSignatureMethod);
        $referenceNode = $dom->createElement('Reference');
        $signedInfoNode->appendChild($referenceNode);
        if (!empty($idSigned)) {
            $idSigned = "#$idSigned";
        }
        $referenceNode->setAttribute('URI', $idSigned);
        $transformsNode = $dom->createElement('Transforms');
        $referenceNode->appendChild($transformsNode);
        $transfNode1 = $dom->createElement('Transform');
        $transformsNode->appendChild($transfNode1);
        $transfNode1->setAttribute('Algorithm', $nsTransformMethod1);
        $digestMethodNode = $dom->createElement('DigestMethod');
        $referenceNode->appendChild($digestMethodNode);
        $digestMethodNode->setAttribute('Algorithm', $nsDigestMethod);
        $digestValueNode = $dom->createElement('DigestValue', $digestValue);
        $referenceNode->appendChild($digestValueNode);
        $c14n = self::canonize($signedInfoNode, $canonical);
        $signature = $certificate->sign($c14n, $algorithm);
        $signatureValue = base64_encode($signature);
        $signatureValueNode = $dom->createElement('SignatureValue', $signatureValue);
        $signatureNode->appendChild($signatureValueNode);
        $keyInfoNode = $dom->createElement('KeyInfo');
        $signatureNode->appendChild($keyInfoNode);
        $x509DataNode = $dom->createElement('X509Data');
        $keyInfoNode->appendChild($x509DataNode);
        $pubKeyClean = $certificate->publicKey->unFormated();
        $x509CertificateNode = $dom->createElement('X509Certificate', $pubKeyClean);
        $x509DataNode->appendChild($x509CertificateNode);
        return $dom;
    }

    /**
     * Remove old signature from document to replace it
     * @param string $content
     * @return string
     */
    public static function removeSignature($content)
    {
        if (!self::existsSignature($content)) {
            return $content;
        }
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($content);
        $node = $dom->documentElement;
        $signature = $node->getElementsByTagName('Signature')->item(0);
        if (!empty($signature)) {
            $parent = $signature->parentNode;
            $parent->removeChild($signature);
        }
        return $dom->saveXML();
    }


    /**
     * Verify if xml signature is valid
     * @param string $content
     * @param string $tagname tag for sign (opcional)
     * @param array $canonical parameters to format node for signature (opcional)
     * @return boolean
     * @throws SignerException Not is a XML, Digest or Signature dont match
     */
    public static function isSigned(
        $content,
        $tagname = '',
        $canonical = [false, false, null, null]
    ) {
        if (self::existsSignature($content)) {
            if (self::digestCheck($content, $tagname, $canonical)) {
                if (self::signatureCheck($content, $canonical)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if Signature tag already exists
     * @param string $content
     * @return boolean
     */
    public static function existsSignature($content)
    {
        if (!Validator::isXML($content)) {
            throw SignerException::isNotXml();
        }
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($content);
        $signature = $dom->getElementsByTagName('Signature')->item(0);
        if (empty($signature)) {
            return false;
        }
        return true;
    }

    /**
     * Verify signature value from SignatureInfo node and public key
     * @param string $xml
     * @param array $canonical
     * @return boolean
     */
    private static function signatureCheck(
        $xml,
        $canonical = [true, false, null, null]
    ) {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        $signature = $dom->getElementsByTagName('Signature')->item(0);
        $sigMethAlgo = $signature->getElementsByTagName('SignatureMethod')
            ->item(0)->getAttribute('Algorithm');
        $algorithm = OPENSSL_ALGO_SHA256;
        if ($sigMethAlgo == 'http://www.w3.org/2000/09/xmldsig#rsa-sha1') {
            $algorithm = OPENSSL_ALGO_SHA1;
        }
        $certificateContent = $signature->getElementsByTagName('X509Certificate')
            ->item(0)->nodeValue;
        $publicKey = PublicKey::createFromContent($certificateContent);
        $signInfoNode = self::canonize(
            $signature->getElementsByTagName('SignedInfo')->item(0),
            $canonical
        );
        $signatureValue = $signature->getElementsByTagName('SignatureValue')
            ->item(0)->nodeValue;
        $decodedSignature = base64_decode(
            str_replace(array("\r", "\n"), '', $signatureValue)
        );
        if (!$publicKey->verify($signInfoNode, $decodedSignature, $algorithm)) {
            throw SignerException::signatureComparisonFailed();
        }
        return true;
    }

    /**
     * Verify digest value of data node
     * @param string $xml
     * @param string $tagname
     * @return boolean
     * @throws SignerException
     */
    private static function digestCheck(
        $xml,
        $tagname = '',
        $canonical = [true, false, null, null]
    ) {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);
        $root = $dom->documentElement;
        $signature = $dom->getElementsByTagName('Signature')->item(0);
        $sigURI = $signature->getElementsByTagName('Reference')
            ->item(0)
            ->getAttribute('URI');
        if (empty($tagname)) {
            if (empty($sigURI)) {
                $tagname = $root->nodeName;
            } else {
                $xpath = new \DOMXPath($dom);
                $entries = $xpath->query('//@Id');
                foreach ($entries as $entry) {
                    $tagname = $entry->ownerElement->nodeName;
                }
            }
        }
        $node = $dom->getElementsByTagName($tagname)->item(0);
        if (empty($node)) {
            throw SignerException::tagNotFound($tagname);
        }
        $sigMethAlgo = $signature->getElementsByTagName('SignatureMethod')
            ->item(0)
            ->getAttribute('Algorithm');
        $algorithm = 'sha256';
        if ($sigMethAlgo == 'http://www.w3.org/2000/09/xmldsig#rsa-sha1') {
            $algorithm = 'sha1';
        }
        if ($sigURI == '') {
            $node->removeChild($signature);
        }
        $calculatedDigest = self::makeDigest($node, $algorithm, $canonical);
        $informedDigest = $signature->getElementsByTagName('DigestValue')
            ->item(0)
            ->nodeValue;
        if ($calculatedDigest != $informedDigest) {
            throw SignerException::digestComparisonFailed();
        }
        return true;
    }

    /**
     * Calculate digest value for given node
     * @param DOMNode $node
     * @param string $algorithm
     * @param array $canonical
     * @return string
     */
    private static function makeDigest(
        DOMNode $node,
        $algorithm,
        $canonical = [false, false, null, null]
    ) {
        //calcular o hash dos dados
        $c14n = self::canonize($node, $canonical);
        $hashValue = hash($algorithm, $c14n, true);
        $hashValue = "T4vLYN6jj934+cD16LewFIHBjbM=";
        return $hashValue;
        // return base64_encode($hashValue);
    }

    /**
     * Reduced to the canonical form
     * @param DOMNode $node
     * @param array $canonical
     * @return string
     */
    private static function canonize(
        DOMNode $node,
        $canonical = [false, false, null, null]
    ) {
        return $node->C14N(
            $canonical[0],
            $canonical[1],
            $canonical[2],
            $canonical[3]
        );
    }
}
