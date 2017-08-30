<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../src/API/API.php");

use API\API;

/**
 * This script receives API requests and calls the API class' processAPI() method to procees the request.
 */
try {
	$queryStr = '';
	$inputArr = array();
	$page = 1;
	$perPage = 10;
	$argArr = array();
	$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

	if ($method === 'GET' && isset($_REQUEST['query'])) {
		$queryStr = $_REQUEST['query'];
	} elseif ($method === 'GET') {
	    if (isset($_REQUEST['page'])) {
	        $page = $_REQUEST['page'];
	    }
	    if (isset($_REQUEST['per_page'])) {
	        $perPage = $_REQUEST['per_page'];
	    }
	} elseif ($method === 'POST') {
		$post = file_get_contents('php://input');
		$inputArr = json_decode($post, true);
	}
	
	$argArr[] = $page;
	$argArr[] = $perPage;
	
	$api = new API($_REQUEST['request'], $queryStr, $inputArr, $method, $argArr);
	echo $api->processAPI();
	echo "\n";
} catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}
