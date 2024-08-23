<?php

namespace NFePHP\NFSe\ISSNET\Factories;

use NFePHP\NFSe\ISSNET\Make;
use NFePHP\Common\Strings;

class Parser
{

    protected $structure;

    protected $make;

    protected $loteRps;

    protected $tomador;

    protected $std;

    public function __construct($version = '3.0.1')
    {

        $ver = str_replace('.', '', $version);

        $path = realpath(__DIR__ . "/../../storage/txtstructure301.json");

        $this->std = new \stdClass();

        $this->lote = new \stdClass();

        $this->identificacaoRps = new \stdClass();

        $this->infRps = new \stdClass();

        $this->servico = new \stdClass();

        $this->valores = new \stdClass();

        $this->lote->tomador = new \stdClass();

        $this->lote->prestador = new \stdClass();

        $this->cabecalho = new \stdClass();

        $this->structure = json_decode(file_get_contents($path), true);

        $this->version = $version;

        $this->make = new Make();
    }

    public function toXml($nota)
    {

        $this->array2xml($nota);

        if ($this->make->monta()) {

            return $this->make->getXML();
        }

        return null;
    }

    protected function array2xml($nota)
    {

        foreach ($nota as $lin) {

            $fields = explode('|', $lin);

            if (empty($fields)) {
                continue;
            }

            $metodo = strtolower(str_replace(' ', '', $fields[0])) . 'Entity';

            if (method_exists(__CLASS__, $metodo)) {

                $struct = $this->structure[strtoupper($fields[0])];

                $std = $this->fieldsToStd($fields, $struct);

                $this->$metodo($std);
            }
        }
    }

    protected function fieldsToStd($dfls, $struct)
    {

        $sfls = explode('|', $struct);

        $len = count($sfls) - 1;

        $std = new \stdClass();

        for ($i = 1; $i < $len; $i++) {

            $name = $sfls[$i];

            if (isset($dfls[$i]))
                $data = $dfls[$i];
            else
                $data = '';

            if (!empty($name)) {

                $std->$name = Strings::replaceSpecialsChars($data);
            }
        }

        return $std;
    }

    private function aEntity($std)
    {
        $this->loteRps = (object) array_merge((array) $this->loteRps, (array) $std);
    }

    private function bEntity($std)
    {
        $cnpj = new \stdClass();

        $cnpj = (object) array_merge((array) $cnpj, (array) $std);

        $this->make->buildCpfCnpjPrestador($cnpj);

        $InscricaoMunicipal = new \stdClass();

        $InscricaoMunicipal = (object) array_merge((array) $InscricaoMunicipal, (array) $std);

        $this->make->buildPrestador($InscricaoMunicipal);

        $this->loteRps = (object) array_merge((array) $this->loteRps, (array) $std);

        $this->make->buildCabec($this->loteRps);
    }

    private function cEntity($std)
    {
    }

    private function eEntity($std)
    {
        $Tomador = new \stdClass();

        $Tomador = (object) array_merge((array) $Tomador, (array) $std);

        $this->make->buildTomador($Tomador);

        $Endereco = new \stdClass();

        $Endereco = (object) array_merge((array) $Endereco, (array) $std);

        $this->make->buildEndereco($Endereco);
    }

    private function e02Entity($std)
    {
        $cnpj = new \stdClass();

        $cnpj = (object) array_merge((array) $cnpj, (array) $std);

        $this->make->buildCpfCnpjTomador($cnpj);
    }

    private function fEntity($std)
    {
    }

    private function hEntity($std)
    {
    }

    private function h01Entity($std)
    {
        $this->identificacaoRps = (object) array_merge((array) $this->identificacaoRps, (array) $std);

        $this->make->buildIdentificacaoRps($this->identificacaoRps);
    }

    private function mEntity($std)
    {
        $this->valores = (object) array_merge((array) $this->valores, (array) $std);

        $this->make->buildValores($this->valores);
    }

    private function nEntity($std)
    {
        $this->servico = (object) array_merge((array) $this->servico, (array) $std);
        
        $this->servico = (object) array_merge((array) $this->servico, (array) $this->valores);

        $this->make->buildServico($this->servico);
    }

    private function wEntity($std)
    {
        $this->infRps = (object) array_merge((array) $this->infRps, (array) $std);

        $this->make->buildInfRps($this->infRps);
    }
}
