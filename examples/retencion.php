<?php


use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Retention\Exchange;
use Greenter\Model\Retention\Payment;
use Greenter\Model\Retention\Retention;
use Greenter\Model\Retention\RetentionDetail;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/../vendor/autoload.php';

// Emisor
$address = new Address();
$address->setUbigueo('150101')
    ->setDepartamento('LIMA')
    ->setProvincia('LIMA')
    ->setDistrito('LIMA')
    ->setUrbanizacion('NONE')
    ->setDireccion('AV LS');

$company = new Company();
$company->setRuc('20000000001')
    ->setRazonSocial('EMPRESA SAC')
    ->setNombreComercial('EMPRESA')
    ->setAddress($address);

$client = new Client();
$client->setTipoDoc('6')
    ->setNumDoc('20000000001')
    ->setRznSocial('EMPRESA 1');

$retention = new Retention();
$retention
    ->setSerie('R001')
    ->setCorrelativo('123')
    ->setFechaEmision(new \DateTime())
    ->setCompany($company)
    ->setProveedor($client)
    ->setObservacion('NOTA /><!-- HI -->')
    ->setImpRetenido(10)
    ->setImpPagado(210)
    ->setRegimen('01')
    ->setTasa(3);

$pay = new Payment();
$pay->setMoneda('PEN')
    ->setFecha(new \DateTime())
    ->setImporte(100);

$cambio = new Exchange();
$cambio->setFecha(new \DateTime())
    ->setFactor(1)
    ->setMonedaObj('PEN')
    ->setMonedaRef('PEN');

$detail = new RetentionDetail();
$detail->setTipoDoc('01')
    ->setNumDoc('F001-1')
    ->setFechaEmision(new \DateTime())
    ->setFechaRetencion(new \DateTime())
    ->setMoneda('PEN')
    ->setImpTotal(200)
    ->setImpPagar(200)
    ->setImpRetenido(5)
    ->setPagos([$pay])
    ->setTipoCambio($cambio);

$retention->setDetails([$detail]);

// Envio a SUNAT.
$see = new \Greenter\See();
$see->setService(SunatEndpoints::RETENCION_BETA);
$see->setCertificate(file_get_contents(__DIR__.'/../resources/cert.pem'));
$see->setCredentials('20000000001MODDATOS', 'moddatos');

$res = $see->send($retention);

if ($res->isSuccess()) {
    /**@var $res \Greenter\Model\Response\BillResult*/
    $cdr = $res->getCdrResponse();

    echo '<h2>Respuesta SUNAT:</h2><br>';
    echo '<b>ID:</b> ' . $cdr->getId().'<br>';
    echo '<b>CODE:</b> ' . $cdr->getCode().'<br>';
    echo '<b>DESCRIPTION:</b> ' . $cdr->getDescription().'<br>';

    // Descomentar para guardar el xml firmado y el CDR de respuesta.
    //    file_put_contents('xml-signed.xml', $see->getFactory()->getLastXml());
    //    file_put_contents('cdr.zip', $res->getCdrZip());
} else {
    var_dump($res->getError());
}