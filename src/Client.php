<?php
namespace Pirsch;

class Client {
	const DEFAULT_BASE_URL = 'https://api.pirsch.io';
	const AUTHENTICATION_ENDPOINT = '/api/v1/token';
	const HIT_ENDPOINT = '/api/v1/hit';
	const EVENT_ENDPOINT = '/api/v1/event';
	const DOMAIN_ENDPOINT = '/api/v1/domain';
    const SESSION_DURATION_ENDPOINT = '/api/v1/statistics/duration/session';
    const TIME_ON_PAGE_ENDPOINT = '/api/v1/statistics/duration/page';
    const UTM_SOURCE_ENDPOINT = '/api/v1/statistics/utm/source';
    const UTM_MEDIUM_ENDPOINT = '/api/v1/statistics/utm/medium';
    const UTM_CAMPAIGN_ENDPOINT = '/api/v1/statistics/utm/campaign';
    const UTM_CONTENT_ENDPOINT = '/api/v1/statistics/utm/content';
    const UTM_TERM_ENDPOINT = '/api/v1/statistics/utm/term';
    const VISITORS_ENDPOINT = '/api/v1/statistics/visitor';
    const PAGES_ENDPOINT = '/api/v1/statistics/page';
    const CONVERSION_GOALS_ENDPOINT = '/api/v1/statistics/goals';
	const EVENTS_ENDPOINT = '/api/v1/statistics/events';
	const EVENT_METADATA_ENDPOINT = '/api/v1/statistics/event/meta';
    const GROWTH_RATE_ENDPOINT = '/api/v1/statistics/growth';
    const ACTIVE_VISITORS_ENDPOINT = '/api/v1/statistics/active';
    const TIME_OF_DAY_ENDPOINT = '/api/v1/statistics/hours';
    const LANGUAGE_ENDPOINT = '/api/v1/statistics/language';
    const REFERRER_ENDPOINT = '/api/v1/statistics/referrer';
    const OS_ENDPOINT = '/api/v1/statistics/os';
    const BROWSER_ENDPOINT = '/api/v1/statistics/browser';
    const COUNTRY_ENDPOINT = '/api/v1/statistics/country';
    const PLATFORM_ENDPOINT = '/api/v1/statistics/platform';
    const SCREEN_ENDPOINT = '/api/v1/statistics/screen';
    const KEYWORDS_ENDPOINT = '/api/v1/statistics/keywords';
	const REFERRER_QUERY_PARAMS = array(
		'ref',
		'referer',
		'referrer',
		'source',
        'utm_source'
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
        if($this->getHeader('DNT') === '1') {
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

	function event($name, $duration = 0, $meta = NULL, $retry = true) {
        if($this->getHeader('DNT') === '1') {
            return;
        }

		$data = array(
			'event_name' => $name,
			'event_duration' => $duration,
			'event_meta' => $meta,
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
		$result = @file_get_contents($this->baseURL.self::EVENT_ENDPOINT, false, $context);
		
		if($result === FALSE) {
			$responseHeader = $http_response_header[0];

			if($this->isUnauthorized($responseHeader) && $retry) {
				$this->refreshToken();
				return $this->hit(false);
			} else {
				throw new \Exception('Error sending event: '.$responseHeader);
			}
		}

		return json_decode($result);
	}

	function domain($retry = true) {
	    if($this->getAccessToken() === '' && $retry) {
            $this->refreshToken();
	    }

	    $options = array(
			'http' => array(
				'method' => 'GET',
				'header' => $this->getRequestHeader()
			)
		);
		$context = stream_context_create($options);
		$result = @file_get_contents($this->baseURL.self::DOMAIN_ENDPOINT, false, $context);

		if($result === FALSE) {
			$responseHeader = $http_response_header[0];

			if($this->isUnauthorized($responseHeader) && $retry) {
				$this->refreshToken();
				return $this->domain(false);
			} else {
				throw new \Exception('Error getting domain: '.$responseHeader);
			}
		}

		$domains = json_decode($result);

		if(count($domains) !== 1) {
		    throw new \Exception('Error reading domain from result');
		}

		return $domains[0];
	}

	function sessionDuration(Filter $filter) {
        return $this->performGet(self::SESSION_DURATION_ENDPOINT, $filter);
	}

	function timeOnPage(Filter $filter) {
        return $this->performGet(self::TIME_ON_PAGE_ENDPOINT, $filter);
    }

    function utmSource(Filter $filter) {
        return $this->performGet(self::UTM_SOURCE_ENDPOINT, $filter);
    }

    function utmMedium(Filter $filter) {
        return $this->performGet(self::UTM_MEDIUM_ENDPOINT, $filter);
    }

    function utmCampaign(Filter $filter) {
        return $this->performGet(self::UTM_CAMPAIGN_ENDPOINT, $filter);
    }

    function utmContent(Filter $filter) {
        return $this->performGet(self::UTM_CONTENT_ENDPOINT, $filter);
    }

    function utmTerm(Filter $filter) {
        return $this->performGet(self::UTM_TERM_ENDPOINT, $filter);
    }

    function visitors(Filter $filter) {
        return $this->performGet(self::VISITORS_ENDPOINT, $filter);
    }

    function pages(Filter $filter) {
        return $this->performGet(self::PAGES_ENDPOINT, $filter);
    }

    function conversionGoals(Filter $filter) {
        return $this->performGet(self::CONVERSION_GOALS_ENDPOINT, $filter);
    }

	function events(Filter $filter) {
        return $this->performGet(self::EVENTS_ENDPOINT, $filter);
    }

	function eventMetadata(Filter $filter) {
        return $this->performGet(self::EVENT_METADATA_ENDPOINT, $filter);
    }

    function growth(Filter $filter) {
        return $this->performGet(self::GROWTH_RATE_ENDPOINT, $filter);
    }

    function activeVisitors(Filter $filter) {
        return $this->performGet(self::ACTIVE_VISITORS_ENDPOINT, $filter);
    }

    function timeOfDay(Filter $filter) {
        return $this->performGet(self::TIME_OF_DAY_ENDPOINT, $filter);
    }

    function languages(Filter $filter) {
        return $this->performGet(self::LANGUAGE_ENDPOINT, $filter);
    }

    function referrer(Filter $filter) {
        return $this->performGet(self::REFERRER_ENDPOINT, $filter);
    }

    function os(Filter $filter) {
        return $this->performGet(self::OS_ENDPOINT, $filter);
    }

    function browser(Filter $filter) {
        return $this->performGet(self::BROWSER_ENDPOINT, $filter);
    }

    function country(Filter $filter) {
        return $this->performGet(self::COUNTRY_ENDPOINT, $filter);
    }

    function platform(Filter $filter) {
        return $this->performGet(self::PLATFORM_ENDPOINT, $filter);
    }

    function screen(Filter $filter) {
        return $this->performGet(self::SCREEN_ENDPOINT, $filter);
    }

    function keywords(Filter $filter) {
        return $this->performGet(self::KEYWORDS_ENDPOINT, $filter);
    }

    private function performGet($url, Filter $filter, $retry = true) {
        if($this->getAccessToken() === '' && $retry) {
            $this->refreshToken();
        }

        $query = http_build_query($filter);
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => $this->getRequestHeader()
            )
        );
        $context = stream_context_create($options);
        $result = @file_get_contents($this->baseURL.$url.'?'.$query, false, $context);

        if($result === FALSE) {
            $responseHeader = $http_response_header[0];

            if($this->isUnauthorized($responseHeader) && $retry) {
                $this->refreshToken();
                return $this->performGet($url, $filter, false);
            } else {
                throw new \Exception('Error getting result for '.$url.': '.$responseHeader);
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
		return 'Authorization: Bearer '.$this->getAccessToken()."\r\n". // These have to be double quotation marks!
			'Content-Type: application/json\r\n';
	}

	private function getAccessToken() {
	    $token = '';

        if(isset($_SESSION['pirsch_access_token'])) {
            $token = $_SESSION['pirsch_access_token'];
        }

        return $token;
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
