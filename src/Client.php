<?php
declare(strict_types=1);

namespace Pirsch;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class Client {
	public const DEFAULT_BASE_URL = 'https://api.pirsch.io';
	public const DEFAULT_TIMEOUT = 5.0;

	public const AUTHENTICATION_ENDPOINT = '/api/v1/token';
	public const HIT_ENDPOINT = '/api/v1/hit';
	public const EVENT_ENDPOINT = '/api/v1/event';
	public const SESSION_ENDPOINT = '/api/v1/session';
	public const DOMAIN_ENDPOINT = '/api/v1/domain';
	public const SESSION_DURATION_ENDPOINT = '/api/v1/statistics/duration/session';
	public const TIME_ON_PAGE_ENDPOINT = '/api/v1/statistics/duration/page';
	public const UTM_SOURCE_ENDPOINT = '/api/v1/statistics/utm/source';
	public const UTM_MEDIUM_ENDPOINT = '/api/v1/statistics/utm/medium';
	public const UTM_CAMPAIGN_ENDPOINT = '/api/v1/statistics/utm/campaign';
	public const UTM_CONTENT_ENDPOINT = '/api/v1/statistics/utm/content';
	public const UTM_TERM_ENDPOINT = '/api/v1/statistics/utm/term';
	public const TOTAL_VISITORS_ENDPOINT = '/api/v1/statistics/total';
	public const VISITORS_ENDPOINT = '/api/v1/statistics/visitor';
	public const PAGES_ENDPOINT = '/api/v1/statistics/page';
	public const ENTRY_PAGES_ENDPOINT = '/api/v1/statistics/page/entry';
	public const EXIT_PAGES_ENDPOINT = '/api/v1/statistics/page/exit';
	public const CONVERSION_GOALS_ENDPOINT = '/api/v1/statistics/goals';
	public const EVENTS_ENDPOINT = '/api/v1/statistics/events';
	public const EVENT_METADATA_ENDPOINT = '/api/v1/statistics/event/meta';
	public const LIST_EVENTS_ENDPOINT = '/api/v1/statistics/event/list';
	public const GROWTH_RATE_ENDPOINT = '/api/v1/statistics/growth';
	public const ACTIVE_VISITORS_ENDPOINT = '/api/v1/statistics/active';
	public const TIME_OF_DAY_ENDPOINT = '/api/v1/statistics/hours';
	public const LANGUAGE_ENDPOINT = '/api/v1/statistics/language';
	public const REFERRER_ENDPOINT = '/api/v1/statistics/referrer';
	public const OS_ENDPOINT = '/api/v1/statistics/os';
	public const OS_VERSION_ENDPOINT = '/api/v1/statistics/os/version';
	public const BROWSER_ENDPOINT = '/api/v1/statistics/browser';
	public const BROWSER_VERSION_ENDPOINT = '/api/v1/statistics/browser/version';
	public const COUNTRY_ENDPOINT = '/api/v1/statistics/country';
	public const REGION_ENDPOINT = '/api/v1/statistics/region';
	public const CITY_ENDPOINT = '/api/v1/statistics/city';
	public const PLATFORM_ENDPOINT = '/api/v1/statistics/platform';
	public const SCREEN_ENDPOINT = '/api/v1/statistics/screen';
	public const TAG_KEYS_ENDPOINT = '/api/v1/statistics/tags';
	public const TAG_DETAILS_ENDPOINT = '/api/v1/statistics/tag/details';
	public const KEYWORDS_ENDPOINT = '/api/v1/statistics/keywords';
	public const LIST_FUNNEL_ENDPOINT = '/api/v1/funnel';
	public const FUNNEL_ENDPOINT = '/api/v1/statistics/funnel';

	public const REFERRER_QUERY_PARAMS = [
		'ref',
		'referer',
		'referrer',
		'source',
		'utm_source',
	];

	public const SESSION_TOKEN_KEY = 'pirsch_access_token';

	private string $clientID;
	private string $clientSecret;
	private ClientInterface $client;
	private string $accessToken = '';

	/**
	 * Creates a new client. To use an access key instead of oAuth credentials, leave the client ID empty
	 * and pass the access key as the client secret.
	 *
	 * @param ClientInterface|null $httpClient optional Guzzle client (base URI and timeout must be configured on it)
	 */
	public function __construct(
		string $clientID,
		string $clientSecret,
		float $timeout = self::DEFAULT_TIMEOUT,
		string $baseURL = self::DEFAULT_BASE_URL,
		?ClientInterface $httpClient = null
	) {
		$this->clientID = $clientID;
		$this->clientSecret = $clientSecret;
		$this->client = $httpClient ?? new HttpClient([
			'base_uri' => $baseURL,
			'timeout' => $timeout,
		]);
	}

	/**
	 * Sends a page view for the current request.
	 *
	 * @deprecated use pageview() instead
	 * @throws PirschException
	 */
	public function hit(): void {
		$this->pageview();
	}

	/**
	 * Sends a page view. Empty fields of $data are filled in from the current request.
	 *
	 * @throws PirschException
	 */
	public function pageview(?HitOptions $data = null): void {
		$data = $this->fillHitOptions($data ?? new HitOptions());
		$this->request('POST', self::HIT_ENDPOINT, $this->hitOptionsToArray($data), 'Error sending page view');
	}

	/**
	 * Sends an event. Empty fields of $data are filled in from the current request.
	 *
	 * @param array<string, scalar>|null $meta
	 * @throws PirschException
	 */
	public function event(string $name, int $duration = 0, ?array $meta = null, ?HitOptions $data = null): void {
		$data = $this->fillHitOptions($data ?? new HitOptions());
		$body = array_merge([
			'event_name' => $name,
			'event_duration' => $duration,
			'event_meta' => $this->stringifyMeta($meta),
		], $this->hitOptionsToArray($data));
		$this->request('POST', self::EVENT_ENDPOINT, $body, 'Error sending event');
	}

	/**
	 * Extends the session of the visitor. Empty fields of $data are filled in from the current request.
	 *
	 * @throws PirschException
	 */
	public function session(?HitOptions $data = null): void {
		$data = $this->fillHitOptions($data ?? new HitOptions());
		$this->request('POST', self::SESSION_ENDPOINT, [
			'ip' => $data->ip,
			'user_agent' => $data->user_agent,
			'sec_ch_ua' => $data->sec_ch_ua,
			'sec_ch_ua_mobile' => $data->sec_ch_ua_mobile,
			'sec_ch_ua_platform' => $data->sec_ch_ua_platform,
			'sec_ch_ua_platform_version' => $data->sec_ch_ua_platform_version,
			'sec_ch_width' => $data->sec_ch_width,
			'sec_ch_viewport_width' => $data->sec_ch_viewport_width,
		], 'Error extending session');
	}

	/**
	 * Returns the domain the client has access to.
	 *
	 * @throws PirschException
	 */
	public function domain(): object {
		$domains = $this->request('GET', self::DOMAIN_ENDPOINT, null, 'Error getting domain');

		if (!is_array($domains) || count($domains) !== 1) {
			throw new PirschException('Error reading domain from result');
		}

		return $domains[0];
	}

	/** @return mixed */
	public function sessionDuration(Filter $filter) {
		return $this->performGet(self::SESSION_DURATION_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function timeOnPage(Filter $filter) {
		return $this->performGet(self::TIME_ON_PAGE_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function utmSource(Filter $filter) {
		return $this->performGet(self::UTM_SOURCE_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function utmMedium(Filter $filter) {
		return $this->performGet(self::UTM_MEDIUM_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function utmCampaign(Filter $filter) {
		return $this->performGet(self::UTM_CAMPAIGN_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function utmContent(Filter $filter) {
		return $this->performGet(self::UTM_CONTENT_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function utmTerm(Filter $filter) {
		return $this->performGet(self::UTM_TERM_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function totalVisitors(Filter $filter) {
		return $this->performGet(self::TOTAL_VISITORS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function visitors(Filter $filter) {
		return $this->performGet(self::VISITORS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function pages(Filter $filter) {
		return $this->performGet(self::PAGES_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function entryPages(Filter $filter) {
		return $this->performGet(self::ENTRY_PAGES_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function exitPages(Filter $filter) {
		return $this->performGet(self::EXIT_PAGES_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function conversionGoals(Filter $filter) {
		return $this->performGet(self::CONVERSION_GOALS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function events(Filter $filter) {
		return $this->performGet(self::EVENTS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function eventMetadata(Filter $filter) {
		return $this->performGet(self::EVENT_METADATA_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function listEvents(Filter $filter) {
		return $this->performGet(self::LIST_EVENTS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function growth(Filter $filter) {
		return $this->performGet(self::GROWTH_RATE_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function activeVisitors(Filter $filter) {
		return $this->performGet(self::ACTIVE_VISITORS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function timeOfDay(Filter $filter) {
		return $this->performGet(self::TIME_OF_DAY_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function languages(Filter $filter) {
		return $this->performGet(self::LANGUAGE_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function referrer(Filter $filter) {
		return $this->performGet(self::REFERRER_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function os(Filter $filter) {
		return $this->performGet(self::OS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function osVersions(Filter $filter) {
		return $this->performGet(self::OS_VERSION_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function browser(Filter $filter) {
		return $this->performGet(self::BROWSER_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function browserVersions(Filter $filter) {
		return $this->performGet(self::BROWSER_VERSION_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function country(Filter $filter) {
		return $this->performGet(self::COUNTRY_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function region(Filter $filter) {
		return $this->performGet(self::REGION_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function city(Filter $filter) {
		return $this->performGet(self::CITY_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function platform(Filter $filter) {
		return $this->performGet(self::PLATFORM_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function screen(Filter $filter) {
		return $this->performGet(self::SCREEN_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function tagKeys(Filter $filter) {
		return $this->performGet(self::TAG_KEYS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function tags(Filter $filter) {
		return $this->performGet(self::TAG_DETAILS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function keywords(Filter $filter) {
		return $this->performGet(self::KEYWORDS_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function listFunnel(Filter $filter) {
		return $this->performGet(self::LIST_FUNNEL_ENDPOINT, $filter);
	}

	/** @return mixed */
	public function funnel(Filter $filter) {
		return $this->performGet(self::FUNNEL_ENDPOINT, $filter);
	}

	/**
	 * @return mixed
	 * @throws PirschException
	 */
	private function performGet(string $url, Filter $filter) {
		return $this->request('GET', $url, null, 'Error getting result for '.$url, $filter->toQuery());
	}

	/**
	 * Sends an authenticated request and decodes the JSON response.
	 * The access token is refreshed and the request retried once on 401.
	 *
	 * @param array<string, mixed>|null $body
	 * @param array<string, mixed> $query
	 * @return mixed
	 * @throws PirschException
	 */
	private function request(string $method, string $url, ?array $body, string $errorMessage, array $query = [], bool $retry = true) {
		if ($this->getAccessToken() === '') {
			$this->refreshToken();
		}

		$options = ['headers' => $this->getRequestHeader()];

		if ($body !== null) {
			$options['json'] = $body;
		}

		if (!empty($query)) {
			$options['query'] = $query;
		}

		try {
			$response = $this->client->request($method, $url, $options);
		} catch (RequestException $e) {
			$response = $e->getResponse();

			if ($response !== null && $response->getStatusCode() === 401 && $retry && $this->clientID !== '') {
				$this->clearAccessToken();
				return $this->request($method, $url, $body, $errorMessage, $query, false);
			}

			throw $this->createException($errorMessage, $e);
		} catch (GuzzleException $e) {
			throw $this->createException($errorMessage, $e);
		}

		$content = (string)$response->getBody();
		return $content === '' ? null : json_decode($content);
	}

	/**
	 * @throws PirschException
	 */
	private function refreshToken(): void {
		$this->clearAccessToken();

		if ($this->clientID === '') {
			throw new PirschException('Single access tokens cannot be refreshed');
		}

		try {
			$response = $this->client->request('POST', self::AUTHENTICATION_ENDPOINT, [
				'json' => [
					'grant_type' => 'client_credentials',
					'client_id' => $this->clientID,
					'client_secret' => $this->clientSecret,
				],
			]);
		} catch (GuzzleException $e) {
			throw $this->createException('Error refreshing token', $e);
		}

		$resp = json_decode((string)$response->getBody());

		if (!is_object($resp) || empty($resp->access_token)) {
			throw new PirschException('Error refreshing token: invalid response', $response->getStatusCode());
		}

		$this->setAccessToken($resp->access_token);
	}

	private function createException(string $message, GuzzleException $e): PirschException {
		$response = $e instanceof RequestException ? $e->getResponse() : null;

		if ($response !== null) {
			return new PirschException(
				$message.' ('.$response->getStatusCode().'): '.$response->getBody(),
				$response->getStatusCode(),
				$e
			);
		}

		return new PirschException($message.': '.$e->getMessage(), null, $e);
	}

	/** @return array<string, string> */
	private function getRequestHeader(): array {
		return [
			'Authorization' => 'Bearer '.$this->getAccessToken(),
			'Content-Type' => 'application/json',
		];
	}

	private function getAccessToken(): string {
		if ($this->clientID === '') {
			return $this->clientSecret;
		}

		if ($this->accessToken === '' && $this->hasSession() && !empty($_SESSION[self::SESSION_TOKEN_KEY])) {
			$this->accessToken = (string)$_SESSION[self::SESSION_TOKEN_KEY];
		}

		return $this->accessToken;
	}

	private function setAccessToken(string $token): void {
		$this->accessToken = $token;

		if ($this->hasSession()) {
			$_SESSION[self::SESSION_TOKEN_KEY] = $token;
		}
	}

	private function clearAccessToken(): void {
		$this->accessToken = '';

		if ($this->hasSession()) {
			unset($_SESSION[self::SESSION_TOKEN_KEY]);
		}
	}

	private function hasSession(): bool {
		return session_status() === PHP_SESSION_ACTIVE;
	}

	private function fillHitOptions(HitOptions $data): HitOptions {
		$data = clone $data;
		$data->url = $this->valueOr($data->url, fn() => $this->getRequestURL());
		$data->ip = $this->valueOr($data->ip, fn() => $this->getHeader('REMOTE_ADDR'));
		$data->user_agent = $this->valueOr($data->user_agent, fn() => $this->getHeader('HTTP_USER_AGENT'));
		$data->accept_language = $this->valueOr($data->accept_language, fn() => $this->getHeader('HTTP_ACCEPT_LANGUAGE'));
		$data->sec_ch_ua = $this->valueOr($data->sec_ch_ua, fn() => $this->getHeader('HTTP_SEC_CH_UA'));
		$data->sec_ch_ua_mobile = $this->valueOr($data->sec_ch_ua_mobile, fn() => $this->getHeader('HTTP_SEC_CH_UA_MOBILE'));
		$data->sec_ch_ua_platform = $this->valueOr($data->sec_ch_ua_platform, fn() => $this->getHeader('HTTP_SEC_CH_UA_PLATFORM'));
		$data->sec_ch_ua_platform_version = $this->valueOr($data->sec_ch_ua_platform_version, fn() => $this->getHeader('HTTP_SEC_CH_UA_PLATFORM_VERSION'));
		$data->sec_ch_width = $this->valueOr($data->sec_ch_width, fn() => $this->getHeader('HTTP_SEC_CH_WIDTH'));
		$data->sec_ch_viewport_width = $this->valueOr($data->sec_ch_viewport_width, fn() => $this->getHeader('HTTP_SEC_CH_VIEWPORT_WIDTH'));
		$data->title = $data->title ?? '';
		$data->referrer = $this->valueOr($data->referrer, fn() => $this->getReferrer());
		$data->screen_width = $data->screen_width ?? 0;
		$data->screen_height = $data->screen_height ?? 0;
		return $data;
	}

	/** @return array<string, mixed> */
	private function hitOptionsToArray(HitOptions $data): array {
		return [
			'url' => $data->url,
			'ip' => $data->ip,
			'user_agent' => $data->user_agent,
			'accept_language' => $data->accept_language,
			'sec_ch_ua' => $data->sec_ch_ua,
			'sec_ch_ua_mobile' => $data->sec_ch_ua_mobile,
			'sec_ch_ua_platform' => $data->sec_ch_ua_platform,
			'sec_ch_ua_platform_version' => $data->sec_ch_ua_platform_version,
			'sec_ch_width' => $data->sec_ch_width,
			'sec_ch_viewport_width' => $data->sec_ch_viewport_width,
			'title' => $data->title,
			'referrer' => $data->referrer,
			'screen_width' => $data->screen_width,
			'screen_height' => $data->screen_height,
			'tags' => $data->tags,
		];
	}

	/**
	 * The API expects event metadata values to be strings.
	 *
	 * @param array<string, scalar>|null $meta
	 * @return array<string, string>|null
	 */
	private function stringifyMeta(?array $meta): ?array {
		if ($meta === null) {
			return null;
		}

		$result = [];

		foreach ($meta as $key => $value) {
			$result[(string)$key] = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;
		}

		return $result;
	}

	private function getRequestURL(): string {
		$https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
		$host = $this->getHeader('HTTP_HOST') ?: $this->getHeader('SERVER_NAME');
		return ($https ? 'https' : 'http').'://'.$host.$this->getHeader('REQUEST_URI');
	}

	private function getReferrer(): string {
		$referrer = $this->getHeader('HTTP_REFERER');

		if ($referrer !== '') {
			return $referrer;
		}

		foreach (self::REFERRER_QUERY_PARAMS as $key) {
			$value = $_GET[$key] ?? '';

			if (is_string($value) && $value !== '') {
				return $value;
			}
		}

		return '';
	}

	private function getHeader(string $name): string {
		return isset($_SERVER[$name]) ? (string)$_SERVER[$name] : '';
	}

	/**
	 * @param callable(): string $default
	 */
	private function valueOr(?string $value, callable $default): string {
		return $value === null || trim($value) === '' ? $default() : $value;
	}
}
