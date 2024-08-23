<?php

namespace NFePHP\NFSe\ISSNET;

use NFePHP\Common\DOMImproved as Dom;
use stdClass;

class Make
{

    public $dom;

    public $xml;

    public function __construct()
    {

        $this->dom = new Dom();

        $this->dom->preserveWhiteSpace = false;

        $this->dom->formatOutput = false;

        $this->enviarLoteRpsEnvio = $this->dom->createElement('EnviarLoteRpsEnvio');

        $this->enviarLoteRpsEnvio->setAttribute('xmlns', 'http://www.abrasf.org.br/nfse.xsd');

        $this->dom->appendChild($this->enviarLoteRpsEnvio);

        $this->loteRps = $this->dom->createElement('LoteRps');
        
        $this->loteRps->setAttribute('versao', '2.04');

        $this->enviarLoteRpsEnvio->appendChild($this->loteRps);

        $this->listaRps = $this->dom->createElement('ListaRps');

        $this->rps = $this->dom->createElement('Rps');

        $this->infRps = $this->dom->createElement('InfDeclaracaoPrestacaoServico');

        $this->rpssecond = $this->dom->createElement('Rps');

        $this->identificacaoRps = $this->dom->createElement('IdentificacaoRps');

        $this->Servico = $this->dom->createElement('Servico');

        $this->Competencia = $this->dom->createElement('Competencia');

        $this->Valores = $this->dom->createElement('Valores');

        $this->Prestador  = $this->dom->createElement('Prestador');

        $this->CpfCnpjPrestador = $this->dom->createElement('CpfCnpj');

        $this->CpfCnpjTomador = $this->dom->createElement('CpfCnpj');

        $this->Tomador = $this->dom->createElement('TomadorServico');

        $this->IdentificacaoTomador = $this->dom->createElement('IdentificacaoTomador');

        $this->Endereco = $this->dom->createElement('Endereco');
        
        $this->RegimeEspecialTributacao = $this->dom->createElement('RegimeEspecialTributacao');
        
        $this->OptanteSimplesNacional = $this->dom->createElement('OptanteSimplesNacional');
        
        $this->IncentivoFiscal = $this->dom->createElement('IncentivoFiscal');
        
        $this->InformacoesComplementares = $this->dom->createElement('InformacoesComplementares');
        
    }

    public function getXML()
    {
        if (empty($this->xml)) {

            $this->monta();
        }

        return $this->xml;
    }

    public function monta()
    {

        $this->loteRps->appendChild($this->listaRps);

        $this->listaRps->appendChild($this->rps);

        $this->rps->appendChild($this->infRps);
 
        $this->rpssecond->insertBefore($this->identificacaoRps,$this->rpssecond->firstChild);
        
        $this->infRps->insertBefore($this->rpssecond, $this->infRps->firstChild);

        // $this->dom->appChild($this->rps, $this->infRps , 'Falta tag "InfRps"');

        // $rps->appendChild($this->infRps);
        //$firstItem = $rps->item(0);

        //$firstItem->insertBefore($this->identificacaoRps, $firstItem->firstChild);

        $this->infRps->appendChild($this->Competencia);

        $this->infRps->appendChild($this->Servico);

        $items = $this->infRps->getElementsByTagName('Servico');

        $firstItem = $items->item(0);

        $firstItem->insertBefore($this->Valores, $firstItem->firstChild);

        $this->infRps->appendChild($this->Prestador);

        $items = $this->infRps->getElementsByTagName('Prestador');

        $firstItem = $items->item(0);

        $firstItem->insertBefore($this->CpfCnpjPrestador, $firstItem->firstChild);

        $this->infRps->appendChild($this->Tomador);

        $items = $this->infRps->getElementsByTagName('TomadorServico');

        $firstItem = $items->item(0);

        $firstItem->insertBefore($this->IdentificacaoTomador, $firstItem->firstChild);

        $this->IdentificacaoTomador->appendChild($this->CpfCnpjTomador);

        $this->Tomador->appendChild($this->Endereco);

        if ($this->RegimeEspecialTributacao->nodeValue != ''){

             $this->infRps->appendChild($this->RegimeEspecialTributacao);

        }

        if ($this->OptanteSimplesNacional->nodeValue != ''){

             $this->infRps->appendChild($this->OptanteSimplesNacional);

        }

        if ($this->IncentivoFiscal->nodeValue != ''){

            $this->infRps->appendChild($this->IncentivoFiscal);

       }

        if ($this->InformacoesComplementares->nodeValue != ''){

             $this->infRps->appendChild($this->InformacoesComplementares);

        }

        $this->xml = $this->dom->saveXML();

        return $this->xml;
    }

    public function buildCabec($std)
    {

        $this->dom->addChild(
            $this->loteRps,
            "NumeroLote",
            $std->NumeroLote,
            true,
            "Número do Lote de RPS"
        );

        $prestador = $this->dom->createElement('Prestador');
        $cpfCnpj = $this->dom->createElement('CpfCnpj');
        
        $prestador->appendChild($cpfCnpj);
        $this->loteRps->appendChild($prestador);
        

        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            trim($std->Cnpj),
            true,
            "Número CNPJ"
        );

        $this->dom->addChild(
            $prestador,
            "InscricaoMunicipal",
            trim($std->InscricaoMunicipal),
            true,
            "Inscrição Municipal"
        );

        $this->dom->addChild(
            $this->loteRps,
            "QuantidadeRps",
            trim($std->QuantidadeRps),
            true,
            "Quantidade de RPS do Lote"
        );

        $this->Competencia->nodeValue = $std->Competencia;
    }

    public function buildIdentificacaoRps($std)
    {

        $this->dom->addChild(
            $this->identificacaoRps,
            "Numero",
            trim($std->Numero),
            true,
            "Número do RPS"
        );

        $this->dom->addChild(
            $this->identificacaoRps,
            "Serie",
            trim($std->Serie),
            true,
            "Número de série do RPS"
        );

        $this->dom->addChild(
            $this->identificacaoRps,
            "Tipo",
            trim($std->Tipo),
            true,
            "Código de tipo de RPS | 1 - RPS | 2 – Nota Fiscal Conjugada (Mista) | 3 – Cupom"
        );
    }

    public function buildInfRps($std)
    {

        $this->dom->addChild(
            $this->rpssecond,
            "DataEmissao",
            trim($std->DataEmissao),
            true,
            "Formato AAAA-MM-DDTHH:mm:ss 
            onde:
            AAAA = ano com 4 caracteres 
            MM = mês com 2 caracteres 
            DD = dia com 2 caracteres 
            T = caractere de formatação que deve existir separando a data da hora 
            HH = hora com 2 caracteres 
            mm: minuto com 2 caracteres 
            ss: segundo com 2 caracteres"
        );

        // $this->dom->addChild(
        //     $this->rpssecond,
        //     "NaturezaOperacao",
        //     trim($std->NaturezaOperacao),
        //     true,
        //     "Código de natureza da operação
        //     1 – Tributação no município
        //     2 - Tributação fora do município
        //     3 - Isenção
        //     4 - Imune
        //     5 –Exigibilidade suspensa por decisão judicial
        //     6 – Exigibilidade suspensa por procedimento administrativo"
        // );

        // $this->dom->addChild(
        //     $this->rpssecond,
        //     "OptanteSimplesNacional",
        //     trim($std->OptanteSimplesNacional),
        //     true,
        //     "Identificação de Sim/Não
        //     1 - Sim
        //     2 – Não"
        // );

        // $this->dom->addChild(
        //     $this->rpssecond,
        //     "IncentivadorCultural",
        //     trim($std->IncentivadorCultural),
        //     true,
        //     "Identificação de Sim/Não
        //     1 - Sim
        //     2 – Não"
        // );

        $this->dom->addChild(
            $this->rpssecond,
            "Status",
            trim($std->Status),
            true,
            "Código de status da NFS-e
            1 – Normal
            2 – Cancelado"
        );

        // $this->dom->addChild(
        //     $this->infRps,
        //     "RegimeEspecialTributacao",
        //     $std->RegimeEspecialTributacao,
        //     true,
        //     "Código de identificação do regime especial de tributação
        //     1 – Microempresa municipal
        //     2 - Estimativa
        //     3 – Sociedade de profissionais
        //     4 – Cooperativa"
        // );

        if ($std->RegimeEspecialTributacao != ''){

            $this->RegimeEspecialTributacao->nodeValue = $std->RegimeEspecialTributacao;
        }

        if ($std->OptanteSimplesNacional != ''){

            $this->OptanteSimplesNacional->nodeValue = $std->OptanteSimplesNacional;
        }

        if ($std->InformacoesComplementares != ''){

            $this->InformacoesComplementares->nodeValue = $std->InformacoesComplementares;
        }

        if ($std->IncentivoFiscal != ''){

            $this->IncentivoFiscal->nodeValue = $std->IncentivoFiscal;
        }
        
    }

    public function buildServico($std)
    {

        $this->dom->addChild(
            $this->Servico,
            "IssRetido",
            trim($std->IssRetido),
            true,
            "dentificação de Sim/Não
            1 - Sim
            2 – Não"
        );

        $this->dom->addChild(
            $this->Servico,
            "ResponsavelRetencao",
            trim($std->ResponsavelRetencao),
            false,
            "Responsavel pela retencao do ISSQN (
                1 - Tomador; 
                2 - Intermediario)"
        );

        $this->dom->addChild(
            $this->Servico,
            "ItemListaServico",
            trim($std->ItemListaServico),
            true,
            "Código de item da lista de serviço"
        );

        $this->dom->addChild(
            $this->Servico,
            "CodigoCnae",
            trim($std->CodigoCnae),
            true,
            "Código CNAE"
        );

        $this->dom->addChild(
            $this->Servico,
            "CodigoTributacaoMunicipio",
            trim($std->CodigoTributacaoMunicipio),
            true,
            "Código de Tributação"
        );

        $this->dom->addChild(
            $this->Servico,
            "CodigoNbs",
            trim($std->CodigoNbs),
            false,
            "Código de Tributação"
        );

        $this->dom->addChild(
            $this->Servico,
            "Discriminacao",
            trim($std->Discriminacao),
            true,
            "Discriminação do conteúdo da NFS-e"
        );

        $this->dom->addChild(
            $this->Servico,
            "CodigoMunicipio",
            trim($std->CodigoMunicipio),
            false,
            ""
        );

        $this->dom->addChild(
            $this->Servico,
            "CodigoPais",
            trim($std->CodigoPais),
            false,
            "Código de identificação do município conforme tabela do IBGE"
        );

        $this->dom->addChild(
            $this->Servico,
            "ExigibilidadeISS",
            trim($std->ExigibilidadeISS),
            true,
            ""
        );

        $this->dom->addChild(
            $this->Servico,
            "IdentifNaoExigibilidade",
            trim($std->IdentifNaoExigibilidade),
            false,
            ""
        );

        $this->dom->addChild(
            $this->Servico,
            "MunicipioIncidencia",
            trim($std->MunicipioIncidencia),
            false,
            ""
        );

        $this->dom->addChild(
            $this->Servico,
            "NumeroProcesso",
            trim($std->NumeroProcesso),
            false,
            ""
        );
    }

       public function buildValores($std)
    {

        $this->dom->addChild(
            $this->Valores,
            "ValorServicos",
            trim($std->ValorServicos),
            true,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "ValorDeducoes",
            trim($std->ValorDeducoes),
            true,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "ValorPis",
            trim($std->ValorPis),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "ValorCofins",
            trim($std->ValorCofins),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "ValorInss",
            trim($std->ValorInss),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "ValorIr",
            trim($std->ValorIr),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "ValorCsll",
            trim($std->ValorCsll),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "OutrasRetencoes",
            trim($std->ValorOutrasRetencoes),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "ValTotTributos",
            trim($std->AliquotaAtributos),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );


        $this->dom->addChild(
            $this->Valores,
            "ValorIss",
            trim(str_replace('-','',$std->ValorIss)),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        if ($std->IssRetido == 2){
            
           
            
        } else {

            // $this->dom->addChild(
            //     $this->Valores,
            //     "ValorIssRetido",
            //     trim(str_replace('-','',$std->ValorIssRetido)),
            //     false,
            //     "Valor monetário.
            //     Formato: 0.00 (ponto separando casa decimal)
            //     Ex:
            //     1.234,56 = 1234.56
            //     1.000,00 = 1000.00
            //     1.000,00 = 1000"
            // );

        }

        // $this->dom->addChild(
        //     $this->Valores,
        //     "BaseCalculo",
        //     trim($std->BaseCalculo),
        //     false,
        //     "Valor monetário.
        //     Formato: 0.00 (ponto separando casa decimal)
        //     Ex:
        //     1.234,56 = 1234.56
        //     1.000,00 = 1000.00
        //     1.000,00 = 1000"
        // );

        try{
            $this->dom->addChild(
                $this->Valores,
                "Aliquota",
                trim($std->Aliquota * 100),
                false,
                "Alíquota. Valor percentual.
                Formato: 0.0000
                Ex:
                1% = 0.01
                25,5% = 0.255
                100% = 1.0000 ou 1"
            );
        } catch(\Exception $e){
            
            $this->dom->addChild(
                $this->Valores,
                "Aliquota",
                '',
                false,
                "Alíquota. Valor percentual.
                Formato: 0.0000
                Ex:
                1% = 0.01
                25,5% = 0.255
                100% = 1.0000 ou 1"
            );

        }

        // $this->dom->addChild(
        //     $this->Valores,
        //     "ValorLiquidoNfse",
        //     trim($std->ValorLiquidoNfse),
        //     false,
        //     "Valor monetário.
        //     Formato: 0.00 (ponto separando casa decimal)
        //     Ex:
        //     1.234,56 = 1234.56
        //     1.000,00 = 1000.00
        //     1.000,00 = 1000"
        // );

        $this->dom->addChild(
            $this->Valores,
            "DescontoIncondicionado",
            trim($std->DescontoIncondicionado),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );

        $this->dom->addChild(
            $this->Valores,
            "DescontoCondicionado",
            trim($std->DescontoCondicionado),
            false,
            "Valor monetário.
            Formato: 0.00 (ponto separando casa decimal)
            Ex:
            1.234,56 = 1234.56
            1.000,00 = 1000.00
            1.000,00 = 1000"
        );
    }

    public function buildPrestador($std)
    {

        $this->dom->addChild(
            $this->Prestador,
            "InscricaoMunicipal",
            trim($std->InscricaoMunicipal),
            true,
            "Inscrição Municipal da empresa/pessoa"
        );
    }

    public function buildTomador($std)
    {

        $this->dom->addChild(
            $this->Tomador,
            "RazaoSocial",
            trim($std->RazaoSocial),
            true,
            "Razão social"
        );
    }

    public function buildCpfCnpjPrestador($std)
    {

        $this->dom->addChild(
            $this->CpfCnpjPrestador,
            "Cnpj",
            trim($std->Cnpj),
            true,
            "Número do Cnpj"
        );
    }

    public function buildCpfCnpjTomador($std)
    {
        if (strlen($std->Cnpj) > 11){
            
            $this->dom->addChild(
                $this->CpfCnpjTomador,
                "Cnpj",
                trim($std->Cnpj),
                true,
                "Número do Cnpj"
            );

        } else {

            $this->dom->addChild(
                $this->CpfCnpjTomador,
                "Cpf",
                trim($std->Cnpj),
                true,
                "Número do Cpf"
            );

        }
    }

    public function buildEndereco($std)
    {

        $this->dom->addChild(
            $this->Endereco,
            "Endereco",
            trim($std->Endereco),
            true,
            "Tipo e nome do logradouro"
        );

        $this->dom->addChild(
            $this->Endereco,
            "Numero",
            trim($std->Numero),
            true,
            "Número do imóvel"
        );

        $this->dom->addChild(
            $this->Endereco,
            "Complemento",
            trim($std->Complemento),
            true,
            "Complemento do Endereço"
        );

        $this->dom->addChild(
            $this->Endereco,
            "Bairro",
            trim($std->Bairro),
            true,
            "Nome do bairro"
        );

        $this->dom->addChild(
            $this->Endereco,
            "CodigoMunicipio",
            trim($std->CodigoMunicipio),
            true,
            "Código da cidade"
        );

        $this->dom->addChild(
            $this->Endereco,
            "Uf",
            trim($std->Uf),
            true,
            "Sigla do estado"
        );

        $this->dom->addChild(
            $this->Endereco,
            "Cep",
            trim($std->Cep),
            true,
            "CEP da localidade"
        );
    }

    public function cancelamento($std)
    {

        $this->dom = new Dom();

        $this->dom->preserveWhiteSpace = false;

        $this->dom->formatOutput = false;

        $req = $this->dom->createElement('CancelarNfseEnvio');
        
        $req->setAttribute('xmlns', 'http://www.abrasf.org.br/nfse.xsd');

        $this->dom->appendChild($req);

        $pedido = $this->dom->createElement('Pedido');

        $infPedidoCancelamento = $this->dom->createElement('InfPedidoCancelamento');

        $identificacaoNfse = $this->dom->createElement('IdentificacaoNfse');

        $this->dom->addChild(
            $identificacaoNfse,
            "Numero",
            $std->Numero,
            true,
            "Número da Nota Fiscal de Serviço Eletrônica, formado pelo ano com 04 (quatro) dígitos e um número seqüencial com 11 posições – 
            Formato AAAANNNNNNNNNNN"
        );

        $CpfCnpj = $this->dom->createElement('CpfCnpj');

        $this->dom->addChild(
            $CpfCnpj,
            "Cnpj",
            $std->cnpj,
            true,
            "Número CNPJ"
        );

        $identificacaoNfse->appendChild($CpfCnpj);

        $this->dom->addChild(
            $identificacaoNfse,
            "InscricaoMunicipal",
            $std->InscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );

        $this->dom->addChild(
            $identificacaoNfse,
            "CodigoMunicipio",
            $std->CodigoMunicipio,
            true,
            "Código de identificação do município conforme tabela do IBGE"
        );

        $infPedidoCancelamento->appendChild($identificacaoNfse);

        $this->dom->addChild(
            $infPedidoCancelamento,
            "CodigoCancelamento",
            $std->CodigoCancelamento,
            true,
            "Código de cancelamento com base na tabela de Erros e alertas."
        );

        $pedido->appendChild($infPedidoCancelamento);

        $req->appendChild($pedido);
        
        $this->dom->appendChild($req);

        $this->xml = $this->dom->saveXML();

        return $this->xml;
    }

    public function consultaSituacao($std){

        $this->dom = new Dom();

        $this->dom->preserveWhiteSpace = false;

        $this->dom->formatOutput = false;

        $req = $this->dom->createElement('ConsultarSituacaoLoteRpsEnvio');

        $req->setAttribute('xmlns', 'http://www.issnetonline.com.br/webserviceabrasf/vsd/servico_consultar_situacao_lote_rps_envio.xsd');
        
        $req->setAttribute('xmlns:tc', 'http://www.issnetonline.com.br/webserviceabrasf/vsd/tipos_complexos.xsd');

        $prestador = $this->dom->createElement('Prestador');

        $cpfCnpj = $this->dom->createElement('CpfCnpj');
        
        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            $std->cnpj,
            true,
            "Número CNPJ"
        );
        
        $prestador->appendChild($cpfCnpj);

        $this->dom->addChild(
            $prestador,
            "InscricaoMunicipal",
            $std->inscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );

        $req->appendChild($prestador);

        $this->dom->addChild(
            $req,
            "Protocolo",
            $std->protocolo,
            true,
            "Protocolo"
        );

        $this->dom->appendChild($req);

        $this->xml = $this->dom->saveXML();

        return $this->xml;
        
    }

    public function consultaNFSePorRPS($std, $prest){

        $this->dom = new Dom();

        $this->dom->preserveWhiteSpace = false;

        $this->dom->formatOutput = false;

        $req = $this->dom->createElement('ConsultarNfseRpsEnvio');

        $Pedido = $this->dom->createElement('Pedido');
        
        $req->setAttribute('xmlns', 'http://www.abrasf.org.br/nfse.xsd');
        
        $identificacaoRps = $this->dom->createElement('IdentificacaoRps');
        
        $this->dom->addChild(
            $identificacaoRps,
            "Numero",
            $std->Numero,
            true,
            "Número RPS"
        );

        $this->dom->addChild(
            $identificacaoRps,
            "Serie",
            $std->Serie,
            true,
            "Serie RPS"
        );

        $this->dom->addChild(
            $identificacaoRps,
            "Tipo",
            $std->Tipo,
            true,
            "Tipo RPS"
        );

        $Pedido->appendChild($identificacaoRps);
        
        $prestador = $this->dom->createElement('Prestador');

        $cpfCnpj = $this->dom->createElement('CpfCnpj');

        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            $prest->cnpj,
            true,
            "Número CNPJ"
        );

        $prestador->appendChild($cpfCnpj);

        $this->dom->addChild(
            $prestador,
            "InscricaoMunicipal",
            $prest->inscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );
        

        $Pedido->appendChild($prestador);

        $req->appendChild($Pedido);

        $this->dom->appendChild($req);

        $this->xml = $this->dom->saveXML();

        return $this->xml;
    }

    public function consultaLoteRPS($nprot, $data){

        $this->dom = new Dom();

        $this->dom->preserveWhiteSpace = false;

        $this->dom->formatOutput = false;

        $req = $this->dom->createElement('ConsultarLoteRpsEnvio');
        
        $req->setAttribute('xmlns', 'http://www.abrasf.org.br/nfse.xsd');
        
        $prestador = $this->dom->createElement('Prestador');

        $cpfCnpj = $this->dom->createElement('CpfCnpj');

        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            $data->cnpj,
            true,
            "Número CNPJ"
        );
        $prestador->appendChild($cpfCnpj);

        $this->dom->addChild(
            $prestador,
            "InscricaoMunicipal",
            $data->inscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );
        

        $req->appendChild($prestador);

        $this->dom->addChild(
            $req,
            "Protocolo",
            $nprot,
            true,
            "Protocolo de aprovacao"
        );

        $this->dom->appendChild($req);

        $this->xml = $this->dom->saveXML();

        return $this->xml;

    }

    public function consulta($std)
    {
        $req = $this->dom->createElement('ConsultarNfseEnvio');
        $req->setAttribute('xmlns', 'http://www.issnetonline.com.br/webserviceabrasf/vsd/servico_consultar_nfse_envio.xsd');
        $req->setAttribute('xmlns:tc', 'http://www.issnetonline.com.br/webserviceabrasf/vsd/tipos_complexos.xsd');
        $this->dom->appendChild($req);

        $prestador = $this->dom->createElement('Prestador');
        $req->appendChild($prestador);

        $cpfCnpj = $this->dom->createElement('CpfCnpj');
        $prestador->appendChild($cpfCnpj);

        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            $std->Cnpj,
            true,
            "Número CNPJ"
        );

        $this->dom->addChild(
            $prestador,
            "InscricaoMunicipal",
            $std->inscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );

        $this->dom->addChild(
            $req,
            "NumeroNfse",
            $std->numeroNfse,
            true,
            "Inscrição Municipal"
        );

        $periodoEmissao = $this->dom->createElement('PeriodoEmissao');
        $req->appendChild($periodoEmissao);

        $this->dom->addChild(
            $periodoEmissao,
            "DataInicial",
            $std->numeroNfse,
            true,
            "Inscrição Municipal"
        );

        $this->dom->addChild(
            $periodoEmissao,
            "DataFinal",
            $std->numeroNfse,
            true,
            "Inscrição Municipal"
        );

        $tomador = $this->dom->createElement('Tomador');
        $req->appendChild($tomador);

        $cpfCnpj = $this->dom->createElement('CpfCnpj');
        $tomador->appendChild($cpfCnpj);

        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            $std->Cnpj,
            true,
            "Número CNPJ"
        );

        $this->dom->addChild(
            $tomador,
            "InscricaoMunicipal",
            $std->inscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );

        $intermediarioServico = $this->dom->createElement('IntermediarioServico');
        $req->appendChild($intermediarioServico);

        $cpfCnpj = $this->dom->createElement('CpfCnpj');
        $intermediarioServico->appendChild($cpfCnpj);

        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            $std->Cnpj,
            true,
            "Número CNPJ"
        );

        $this->dom->addChild(
            $intermediarioServico,
            "RazaoSocial",
            $std->RazaoSocial,
            true,
            "Razão Social"
        );

        $this->dom->addChild(
            $intermediarioServico,
            "InscricaoMunicipal",
            $std->inscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );

        $this->xml = $this->dom->saveXML();

        return $this->xml;
    }

    public function ConsultarUrlNfseEnvio($NumeroNfse, $data){

        $this->dom = new Dom();

        $this->dom->preserveWhiteSpace = false;

        $this->dom->formatOutput = false;

        $req = $this->dom->createElement('ConsultarUrlNfseEnvio');
        
        $Pedido = $this->dom->createElement('Pedido');

        $req->setAttribute('xmlns', 'http://www.abrasf.org.br/nfse.xsd');

        $prestador = $this->dom->createElement('Prestador');

        $cpfCnpj = $this->dom->createElement('CpfCnpj');

        $this->dom->addChild(
            $cpfCnpj,
            "Cnpj",
            $data->cnpj,
            true,
            "Número CNPJ"
        );

        $prestador->appendChild($cpfCnpj);

        $this->dom->addChild(
            $prestador,
            "InscricaoMunicipal",
            $data->inscricaoMunicipal,
            true,
            "Inscrição Municipal"
        );
        

        $Pedido->appendChild($prestador);

        $this->dom->addChild(
            $Pedido,
            "NumeroNfse",
            $NumeroNfse,
            true,
            "NumeroNfse"
        );

        $this->dom->addChild(
            $Pedido,
            "Pagina",
            1,
            true,
            "Pagina"
        );

        $req->appendChild($Pedido);

        $this->dom->appendChild($req);

        $this->xml = $this->dom->saveXML();

        return $this->xml;

    }
}
