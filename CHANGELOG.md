# Changelog

## 3.0.0

* added strict types, typed properties, and return types
* added `PirschException` (thrown for all errors, includes the HTTP status code)
* added `meta` and `tags` filters to `Filter`
* added `Filter::SCALE_*` and `Filter::CUSTOM_METRIC_TYPE_*` constants
* added optional Guzzle client to the `Client` constructor
* `hit()` is deprecated, use `pageview()` instead
* `pageview()`, `event()`, and `session()` no longer return a value
* `pageview()` and `session()` can be called without arguments
* the access token is cached on the client and only stored in `$_SESSION` if a session is active
* fixed `session()` retry passing a boolean instead of the options
* fixed errors being silently ignored on network failures
* fixed broken error message when refreshing the token fails
* fixed `HTTPS=off` being detected as HTTPS
* fixed `HitOptions` passed by the caller being modified
* removed `require` of the autoloader from `Client.php`
* removed hardcoded credentials from the demo

## 2.0.2

* updated dependencies

## 2.0.1

* fixed unsetting access token stored inside session on error

## 2.0.0

* added funnel endpoints
* added options to extending sessions
* updated dependencies

## 1.12.0

* added regional statistics
* removed DNT
* updated dependencies

## 1.11.0

* added tags to page views
* added reading tag statistics
* updated dependencies

## 1.10.7

* fixed timeout
* don't store base URL and timeout in Client class

## 1.10.6

* fixed Guzzle JSON parameters

## 1.10.5

* fixed import path

## 1.10.4

* fixed import path

## 1.10.3

* added Guzzle for HTTP requests
* added vendor directory

## 1.10.2

* improved error handling

## 1.10.1

* fixed extending sessions
* improved error handling

## 1.10.0

* added custom_metric_key and custom_metric_type to filter

## 1.9.0

* added optional client hint headers

## 1.8.1

* added missing fields
* fixed page view and event request parameters
* fixed PHP 8.1 warnings

## 1.8.0

* added missing fields to filter
* added HTTP timeout configuration (default is 5 seconds)
* removed hostname

## 1.7.0

* removed IP header

## 1.6.4

* added single access token that don't require to query an access token using oAuth

## 1.6.3

* added `HitOptions` to event method

## 1.6.2

* fixed empty check for `pageview` method
* fixed method calls after refreshing token

## 1.6.1

* added missing `HitOptions` fields

## 1.6.0

* added `pageview` method as an alternative to `hit` that can be used to pass in custom URL and referrer

## 1.5.0

* added listing events, os and browser versions
* added filter options

## 1.4.1

* added endpoint for total visitor statistics

## 1.4.0

* added endpoint to extend sessions
* added entry page statistics
* added exit page statistics
* added number of sessions to referrer statistics
* added city statistics
* added entry page, exit page, city, and referrer name to filter

## 1.3.0

* added method to send events
* added reading event statistics
* fixed filter parameters to read statistics

## 1.2.0

* added `source` and `utm_source` to referrers
* added methods to read statistics

## 1.1.2

* added missing DNT (do not track) header

## 1.1.1

* fixed request URI slashes

## 1.1.0

* added composer autoload capability
* added namespace

## 1.0.0

Initial release.
