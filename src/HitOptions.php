<?php
namespace Pirsch;

// DNT header is not required, as it will be checked before making the request.
class HitOptions {
	public $url;
	public $ip;
	public $user_agent;
	public $accept_language;
	public $title;
	public $referrer;
	public $screen_width;
	public $screen_height;
}
