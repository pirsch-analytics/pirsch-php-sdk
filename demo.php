<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('default_socket_timeout', 2);
error_reporting(E_ALL);
session_start();
session_destroy();

require_once "src/Client.php";
require_once "src/Filter.php";

$clientID = 'O8M06LMMjrxdX02foKceD7avBwLdFfnz';
$clientSecret = 'BKw88hmG8CviHVhoxRF7WJMk9lJpe8KDwMJ8fgg0Eaep0KU6bXYhSjKHm7CjKHIe';
$client = new Pirsch\Client($clientID, $clientSecret, 'pirsch.io', 'http://localhost.com:9999');

/*try {
	$client->hit();
	print '<p>Hit sent!</p>';
} catch(Exception $e) {
	print '<p>An error occurred while sending hit: </p>'.$e->getMessage();
}*/

try {
    $domain = $client->domain();
    var_dump($domain);
    echo '<br /><br />';

    $filter = new Pirsch\Filter();
    $filter->id = $domain->id;
    $filter->from = "2021-06-19";
    $filter->to = "2021-06-26";
    var_dump($filter);
    echo '<br /><br />';

    $sessionDuration = $client->sessionDuration($filter);
    var_dump($sessionDuration);
    echo '<br /><br />';

    $timeOnPage = $client->timeOnPage($filter);
    var_dump($timeOnPage);
    echo '<br /><br />';

    $utmSource = $client->utmSource($filter);
    var_dump($utmSource);
    echo '<br /><br />';

    $utmMedium = $client->utmMedium($filter);
    var_dump($utmMedium);
    echo '<br /><br />';

    $utmCampaign = $client->utmCampaign($filter);
    var_dump($utmCampaign);
    echo '<br /><br />';

    $utmContent = $client->utmContent($filter);
    var_dump($utmContent);
    echo '<br /><br />';

    $utmTerm = $client->utmTerm($filter);
    var_dump($utmTerm);
    echo '<br /><br />';

    $visitors = $client->visitors($filter);
    var_dump($visitors);
    echo '<br /><br />';

    $pages = $client->pages($filter);
    var_dump($pages);
    echo '<br /><br />';

    $conversionGoals = $client->conversionGoals($filter);
    var_dump($conversionGoals);
    echo '<br /><br />';

    $growth = $client->growth($filter);
    var_dump($growth);
    echo '<br /><br />';

    $activeVisitors = $client->activeVisitors($filter);
    var_dump($activeVisitors);
    echo '<br /><br />';

    $timeOfDay = $client->timeOfDay($filter);
    var_dump($timeOfDay);
    echo '<br /><br />';

    $languages = $client->languages($filter);
    var_dump($languages);
    echo '<br /><br />';

    $referrer = $client->referrer($filter);
    var_dump($referrer);
    echo '<br /><br />';

    $os = $client->os($filter);
    var_dump($os);
    echo '<br /><br />';

    $browser = $client->browser($filter);
    var_dump($browser);
    echo '<br /><br />';

    $country = $client->country($filter);
    var_dump($country);
    echo '<br /><br />';

    $platform = $client->platform($filter);
    var_dump($platform);
    echo '<br /><br />';

    $screen = $client->screen($filter);
    var_dump($screen);
    echo '<br /><br />';

    $keywords = $client->keywords($filter);
    var_dump($keywords);
    echo '<br /><br />';
} catch(Exception $e) {
    print '<p>An error occurred while reading the statistics: </p>'.$e->getMessage();
}
