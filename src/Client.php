<?php
namespace Pirsch;

class Client {
	const DEFAULT_BASE_URL = 'https://api.pirsch.io';
	const AUTHENTICATION_ENDPOINT = '/api/v1/token';
	const HIT_ENDPOINT = '/api/v1/hit';
	const REFERRER_QUERY_PARAMS = array(
		'ref',
		'referer',
		'referrer',
	);

	private $baseURL;
	private $clientID;
	private $clientSecret;
	private $hostname;

	function __construct($clientID, $clientSecret, $hostname, $baseURL = self::DEFAULT_BASE_URL) {
		$this->baseURL = $baseURL;
		$this->clientID = $clientID;
		$this->clientSecret = $clientSecret;
		$this->hostname = $hostname;
	}

	function hit($retry = true) {
        if($this->getHeader("DNT") === '1') {
            return;
        }

		$data = array(
			'hostname' => $this->hostname,
			'url' => $this->getRequestURL(),
			'ip' => $this->getHeader('REMOTE_ADDR'),
			'cf_connecting_ip' => $this->getHeader('HTTP_CF_CONNECTING_IP'),
			'x_forwarded_for' => $this->getHeader('HTTP_X_FORWARDED_FOR'),
			'forwarded' => $this->getHeader('HTTP_FORWARDED'),
			'x_real_ip' => $this->getHeader('HTTP_X_REAL_IP'),
			'user_agent' => $this->getHeader('HTTP_USER_AGENT'),
			'accept_language' => $this->getHeader('HTTP_ACCEPT_LANGUAGE'),
			'referrer' => $this->getReferrer()
		);
		$options = array(
			'http' => array(
				'method' => 'POST',
				'header' => $this->getRequestHeader(),
				'content' => json_encode($data)
			)
		);
		$context = stream_context_create($options);
		$result = @file_get_contents($this->baseURL.self::HIT_ENDPOINT, false, $context);
		
		if($result === FALSE) {
			$responseHeader = $http_response_header[0];

			if($this->isUnauthorized($responseHeader) && $retry) {
				$this->refreshToken();
				return $this->hit(false);
			} else {
				throw new \Exception('Error sending hit: '.$responseHeader);
			}
		}

		return json_decode($result);
	}

	private function refreshToken() {
		$data = array(
			'grant_type' => 'client_credentials',
			'client_id' => $this->clientID,
			'client_secret' => $this->clientSecret
		);
		$options = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
				'content' => json_encode($data)
			)
		);
		$context = stream_context_create($options);
		$result = @file_get_contents($this->baseURL.self::AUTHENTICATION_ENDPOINT, false, $context);
		
		if($result === FALSE) {
			throw new \Exception('Error refreshing token: '.$http_response_header[0]);
		}

		$resp = json_decode($result);
		$_SESSION['pirsch_access_token'] = $resp->access_token;
	}

	private function getRequestHeader() {
		$token = '';

		if(isset($_SESSION['pirsch_access_token'])) {
			$token = $_SESSION['pirsch_access_token'];
		}

		return "Authorization: Bearer ".$token."\r\n".
			"Content-Type: application/json\r\n";
	}

	private function isUnauthorized($header) {
		return strpos($header, '401') !== FALSE;
	}

	private function getRequestURL() {
		return 'http'.(isset($_SERVER['HTTPS']) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}

	private function getReferrer() {
		$referrer = $this->getHeader('HTTP_REFERER');

		if(empty($referrer)) {
			foreach(self::REFERRER_QUERY_PARAMS as $key) {
				$referrer = $this->getQueryParam($key);

				if($referrer != '') {
					return $referrer;
				}
			}
		}

		return $referrer;
	}

	private function getHeader($name) {
		if(isset($_SERVER[$name])) {
			return $_SERVER[$name];
		}

		return '';
	}

	private function getQueryParam($name) {
		if(isset($_GET[$name])) {
			return $_GET[$name];
		}

		return '';
	}
}
