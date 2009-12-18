<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

$uri = 'http://phpproxy.local/server.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)) . '/libraries/');

/**
 * For handling the HTTP connection to the phpproxy service
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * For handling the HTTP connection through the cURL
 * @see Zend_Http_Client_Adapter_Curl
 */
require_once 'Zend/Http/Client/Adapter/Curl.php';

iconv_set_encoding('input_encoding', 'UTF-8');
iconv_set_encoding('output_encoding', 'UTF-8');
iconv_set_encoding('internal_encoding', 'UTF-8');

$http = new Zend_Http_Client();
$http->setAdapter('Zend_Http_Client_Adapter_Curl');

if($http->getUri() === null) {
	$result = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
	if ($result) {
		$result = '?' . $result;
	}
	$http->setUri($uri . $result);
}

$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
$header[] = "Cache-Control: max-age=0";
$header[] = "Connection: keep-alive";
$header[] = "Keep-Alive: 300";
$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
$header[] = "Accept-Language: en-us,en;q=0.5";
$header[] = "Pragma: "; // browsers keep this blank.

$http->setHeaders($header);

// POST, PUT
//$http->setParameterPost('data', '<PutTimeOffset>true</PutTimeOffset>');
//$http->setEncType(Zend_Http_Client::ENC_URLENCODED);
//$httpResponse = $http->request(Zend_Http_Client::POST);
//$httpResponse = $http->request(Zend_Http_Client::PUT);

//add a raw data if you want
//$http->setRawData('<PutTimeOffset>true</PutTimeOffset>', Zend_Http_Client::ENC_FORMDATA);

//delete
//$httpResponse = $http->request(Zend_Http_Client::DELETE);

//get
$httpResponse = $http->request(Zend_Http_Client::GET);

if (! $httpResponse->isSuccessful()) {
	print 'Error getting response';
}

// maybe you want to see whole request
if (false) {
	print $http->getLastRequest();
}

$responseHeaders = $httpResponse->getHeaders();
unset($responseHeaders['Content-encoding']);
unset($responseHeaders['Vary']);

foreach ($responseHeaders as $responseHeaderName => $responseHeaderValue) {
	header($responseHeaderName . ':' . $responseHeaderValue);
}

print $httpResponse->getBody();
