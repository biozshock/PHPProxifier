<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

$proxyingUrl = 'http://php.net';
$debug = false;

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__)) . '/libraries/');

/**
 * For handling the HTTP connection to the phpproxy service
 * @see Zend_Http_Client
 */
require_once 'Zend/Controller/Request/Http.php';

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

$request = new Zend_Controller_Request_Http();

iconv_set_encoding('input_encoding', 'UTF-8');
iconv_set_encoding('output_encoding', 'UTF-8');
iconv_set_encoding('internal_encoding', 'UTF-8');

$http = new Zend_Http_Client();
$http->setAdapter('Zend_Http_Client_Adapter_Curl');

if($http->getUri() === null) {
	$http->setUri($proxyingUrl . '/' . $request->getParam('proxyingUri'));
	unset($_GET['proxyingUri']);
}


$headers = array();
$headers[] = 'Accept-encoding: ' . $request->getHeader('Accept-encoding');
$headers[] = 'User-Agent: ' . $request->getHeader('User-Agent');
$headers[] = 'Accept: ' . $request->getHeader('Accept');
$headers[] = 'Cache-Control: ' . $request->getHeader('Cache-Control');
$headers[] = 'Connection: ' . $request->getHeader('Connection');
$headers[] = 'Keep-Alive: ' . $request->getHeader('Keep-Alive');
$headers[] = 'Accept-Charset: ' . $request->getHeader('Accept-Charset');
$headers[] = 'Accept-Language: ' . $request->getHeader('Accept-Language');

$http->setHeaders($headers);

$request->getHeader('Content-Type') == 'application/x-www-form-urlencoded' ?
	$http->setEncType(Zend_Http_Client::ENC_URLENCODED) :
	$http->setEncType(Zend_Http_Client::ENC_FORMDATA);

if ($request->getMethod() == 'PUT') {
	$fh = fopen('php://input', 'r');
	if (!$fh) {
		echo 'Can\'t load PUT data';
		die;
	}

	$data = '';
	while (!feof($fh)) {
		$data .= fgets($fh);
	}
	fclose($fh);
	$http->setRawData($data);
}

foreach ($_POST as $k => $v) {
	$http->setParameterPost($k, $v);
}

foreach ($_GET as $k => $v) {
	$http->setParameterGet($k, $v);
}

$httpResponse = $http->request($request->getMethod());

$responseHeaders = $httpResponse->getHeaders();
//var_dump($responseHeaders);die;
foreach ($responseHeaders as $responseHeaderName => $responseHeaderValue) {
	header($responseHeaderName . ':' . $responseHeaderValue);
}

echo $httpResponse->getBody();

// debug
if ($debug) {

	echo 'Method: ', $request->getMethod(), '<br/>', "\n";
	echo 'Headrs: <br/>', "\n";
	echo '        Accept: ', $request->getHeader('accept'), '<br/>', "\n";
	echo '        User-agent: ', $request->getHeader('user-agent'), '<br/>', "\n";
	echo '        Accept-charset: ', $request->getHeader('accept-charset'), '<br/>', "\n";
	echo '        Accept-language: ', $request->getHeader('accept-language'), '<br/>', "\n";
	
	if ($request->getMethod() == 'PUT') {
		$fh = fopen('php://input', 'r');
		if (!$fh) {
			echo 'Can\'t load PUT data';
			die;
		}
	
		$data = '';
		while (!feof($fh)) {
			$data .= fgets($fh);
		}
		fclose($fh);
	} else {
		$data = print_r($request->getParams(), true);
	}
	
	echo 'Data: ', $data;
	
	echo '<br/>', "\n", 'Server vars: ', '<br/>', "\n";
	
	echo '<br/>', "\n", '$_SERVER: ', '<br/>', "\n";
	print_r($_SERVER);
	
	echo '<br/>', "\n", '$_ENV: ', '<br/>', "\n";
	print_r($_ENV);
	
	echo '<br/>', "\n", '$_POST: ', '<br/>', "\n";
	print_r($_POST);
	
	echo '<br/>', "\n", '$_GET: ', '<br/>', "\n";
	print_r($_GET);
}