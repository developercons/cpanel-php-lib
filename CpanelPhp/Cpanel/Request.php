<?php
namespace CpanelPhp\Cpanel;


class Request
{
    private $url = "";
    private $port = "";
    private $username = "";
    private $password = "";

    public $query = "";

    public $response;

    public function __construct($url = null, $port = null, $username = null, $password = null, $options = array())
    {
        if ($url) {
            $this->url = $url;
        } else {
            $this->url = WHMURL;
        }

        if ($port) {
            $this->port = $port;
        } else {
            $this->port = WHMPORT;
        }

        if ($username) {
            $this->username = $username;
        } else {
            $this->username = WHMUSERNAME;
        }

        if ($password) {
            $this->password = $password;
        } else {
            $this->password = WHMPASSWORD;
        }
    }

    public function build($command, $options = array())
    {
        $curl = curl_init();                                // Create Curl Object
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER, 0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($this->username . ":" . $this->password) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        $queryUrl = $this->buildUrl($this->url, $this->port, $command, $options);
        curl_setopt($curl, CURLOPT_URL, $queryUrl);            // execute the query
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $queryUrl");
            // log error if curl exec fails
        }
        curl_close($curl);
        $this->response = $result;

        return new Response($this->response);
    }

    public function buildUrl($url, $port, $command, $options = array())
    {
        $requestType = 'json-api';
        $apiVersion = 1;

        $urlParams = array('api.version' => 1);

        if(in_array('type', array_keys($options))){
            $requestType = $options['type'];
        }

        if(in_array('api.version', array_keys($options))){
            $apiVersion = $options['api.version'];
        }

        if(in_array('params', array_keys($options))){
            $urlParams = array_merge($options['params'], array('api.version' => $apiVersion));
        }

        $query = $url . ":" . $port . "/" . $requestType . "/" . $command . "?" . http_build_query($urlParams,
                '', '&');;
        $this->query = $query;

        return $query;
    }
}