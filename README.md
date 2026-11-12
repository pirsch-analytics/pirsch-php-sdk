# Pirsch PHP SDK

This is the official PHP client SDK for Pirsch. For details, please check out our [documentation](https://docs.pirsch.io/).

## Installation

```bash
composer require pirsch-analytics/sdk
```

PHP 7.4 or later is required.

## Usage

### Creating a client

Create a client in the Pirsch dashboard (domain settings, *Integration* tab). You can use either oAuth credentials (client ID + secret) or a single access key.

```php
<?php
require __DIR__.'/vendor/autoload.php';

// oAuth client (can read statistics and send data)
$client = new Pirsch\Client('client_id', 'client_secret');

// Access key (write-only): leave the client ID empty and pass the key as the secret
$client = new Pirsch\Client('', 'pa_access_key');

// Optional: custom timeout (seconds) and base URL
$client = new Pirsch\Client('client_id', 'client_secret', 2.0, Pirsch\Client::DEFAULT_BASE_URL);
```

oAuth access tokens are cached on the client instance and, if a PHP session is active, in `$_SESSION` so they can be reused across requests. Call `session_start()` before using the client to benefit from that.

### Tracking page views

Call `pageview()` while handling a request. The URL, IP, user agent, referrer, and other headers are read from the current request automatically.

```php
try {
    $client->pageview();
} catch (Pirsch\PirschException $e) {
    error_log($e->getMessage());
}
```

You can override any of the values using `HitOptions`. Fields left empty are filled in from the current request.

```php
$options = new Pirsch\HitOptions();
$options->title = 'My Page';
$options->tags = ['author' => 'John'];
$client->pageview($options);
```

### Tracking events

```php
// name, duration in seconds, metadata, optional HitOptions
$client->event('Signup', 0, ['plan' => 'pro']);
```

### Extending sessions

Keeps the visitor's session alive without sending a page view.

```php
$client->session();
```

### Reading statistics

Statistics require oAuth credentials. Get the domain first and use its ID for the filter.

```php
$domain = $client->domain();

$filter = new Pirsch\Filter();
$filter->id = $domain->id;
$filter->from = date('Y-m-d', strtotime('-7 days'));
$filter->to = date('Y-m-d');
$filter->scale = Pirsch\Filter::SCALE_DAY;

$visitors = $client->visitors($filter);
$pages = $client->pages($filter);

// Filter by event metadata or tags
$filter->event = 'Signup';
$filter->meta = ['plan' => 'pro'];
$events = $client->events($filter);
```

Results are returned as decoded JSON (`stdClass` objects / arrays). All available methods can be found in [src/Client.php](src/Client.php), and a full example in [demo.php](demo.php).

### Error handling

All methods throw a `Pirsch\PirschException` on failure. Use `getStatusCode()` to get the HTTP status code returned by the API, if any.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT
