<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('default_socket_timeout', 2);
error_reporting(E_ALL);
session_start();
//session_destroy();

require_once "pirsch.php";

$clientID = 'RFI87s6avrF8609FBQKu3Ah2kBTWHkqc';
$clientSecret = 'RmduRfzhJ3M2zxBNqNOMUlxxrDy7RA3u8qaUK1goWQuMM4YWPHBVp1N0K8po8Cfz';

try {
	$client = new PirschClient($clientID, $clientSecret, 'pirsch.io', 'http://localhost.com:9999');
	$client->hit();

	print '<p>Hit sent!</p>';
} catch(Exception $e) {
	print '<p>An error occurred while sending hit: </p>'.$e->getMessage();
}
