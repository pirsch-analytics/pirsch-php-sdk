<?php
declare(strict_types=1);

namespace Pirsch;

/**
 * Optional data for page views, events, and sessions.
 * Fields left empty are filled in from the current request.
 */
class HitOptions {
	public ?string $url = null;
	public ?string $ip = null;
	public ?string $user_agent = null;
	public ?string $accept_language = null;
	public ?string $sec_ch_ua = null;
	public ?string $sec_ch_ua_mobile = null;
	public ?string $sec_ch_ua_platform = null;
	public ?string $sec_ch_ua_platform_version = null;
	public ?string $sec_ch_width = null;
	public ?string $sec_ch_viewport_width = null;
	public ?string $title = null;
	public ?string $referrer = null;
	public ?int $screen_width = null;
	public ?int $screen_height = null;

	/** @var array<string, string>|null */
	public ?array $tags = null;
}
