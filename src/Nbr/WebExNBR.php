<?php
/**
 * Created by PhpStorm.
 * User: Sathishkumar Rakkiasamy
 * Date: 9/20/2017
 * Time: 3:41 AM
 */

namespace Sathish\Webex\Nbr;

/**
 * Class WebEXNBR
 * @package Sathish\Webex\Nbr
 */
class WebExNBR
{

    /**
     * @var string $adminUsername - WebEX admin username
     */
    private $adminUsername;

    /**
     * @var string $adminPassword - WebEx admin password
     */
    private $adminPassword;

    /**
     * @var integer siteId - WebEX site id
     */
    private $siteId;

    /**
     * @var string $url - NBR API URL(it differs based on WebEx zone)
     */
    private $serviceURL;

    /**
     * @var string $serviceName - NBR API service name
     */
    private $serviceName;

    /**
     * @var string $xmlBody - SOAP XML body content
     */
    private $xmlBody;

    /**
     * @var string $xml - SOAP XML content
     */
    private $xml;

    /**
     * @var string $ticket - NBR storage ticket
     */
    private $ticket;

    /**
     * WebEXNBR constructor.
     * @param $adminUsername
     * @param $adminPassword
     * @param $siteId
     */
    public function __construct($adminUsername, $adminPassword, $siteId)
    {
        $this->adminUsername = $adminUsername;
        $this->adminPassword = $adminPassword;
        $this->siteId = $siteId;
    }

    public function setServiceUrl($url)
    {
        $this->serviceURL = $url;
    }

    /**
     * Method to generate the storage access ticket;
     *
     * @return void
     */
    public function generateTicket()
    {
        $this->serviceName = 'NBRStorageService';
        $this->constructBody('getStorageAccessTicket', ['siteId ' => $this->siteId, 'username' => $this->adminUsername, 'password' => $this->adminPassword]);
        $response = $this->sendRequest();
        $dom = new \DOMDocument();
        $dom->loadXML($response);
        $this->ticket = $dom->getElementsByTagName('getStorageAccessTicketReturn')->item(0)->nodeValue;
    }

    /**
     * Method to refresh the storage access ticket token expired after 1 hour
     *
     * @return void
     */
    public function refreshTicket()
    {
        $this->generateTicket();
    }

    /**
     * Method to download the recording(Multipart) from NBR server.
     *
     * @param int $recordID
     * @param int $retry
     * @return array
     * @throws \Exception
     */
    public function downloadRecording($recordID, $retry = 1)
    {
        $this->serviceName = 'NBRStorageService';
        $this->constructBody('downloadNBRStorageFile', ['recordId' => $recordID, 'siteID' => $this->siteId, 'ticket' => $this->ticket]);
        return $this->sendRequest();
    }

    /**
     * Method to generate SOAP Body XML
     *
     * @param string $method
     * @param array $data
     * @return void
     */
    public function constructBody($method, array $data)
    {
        $this->xmlBody = '<soapenv:Body><ns1:' . $method .  ' xmlns:ns1="' . $this->serviceName . '">';
        foreach ($data as $key => $value) {
            $this->xmlBody .= '<' . $key .'>' . $value . '</' . $key . '>';
        }
        $this->xmlBody .= '</ns1:' . $method . '></soapenv:Body>';
        $this->constructXml();
    }

    /**
     * Method to generate SOAP XML
     *
     * @return void
     */
    public function constructXml()
    {
        $this->xml = '<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
        $this->xml .= $this->xmlBody;
        $this->xml .= '</soapenv:Envelope>';
    }

    /**
     * Method to send the CURL request to WebEx NBR Server
     *
     * @return string
     */
    public function sendRequest()
    {
        if (!isset($this->serviceURL)) {
            throw new \Exception('Service URL is not set. Use setServiceUrl() method to add service URL.');
        }
        $url = $this->serviceURL . '/' . $this->serviceName;
        //open connection
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type:text/xml', 'SOAPAction:" "']);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, "$this->xml" );
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_status == 200) {
            return $result;
        } elseif ($http_status == 500) {
            $dom = new \DOMDocument();
            $dom->loadXML($result);
            throw new \Exception($dom->getElementsByTagName('faultstring')->item(0)->nodeValue);
        }
        return $result;
    }
}
