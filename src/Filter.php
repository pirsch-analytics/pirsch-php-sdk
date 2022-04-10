<?php
namespace Pirsch;

const SCALE_DAY = 'day';
const SCALE_WEEK = 'week';
const SCALE_MONTH = 'month';
const SCALE_YEAR = 'year';

class Filter {
    public $id;
	public $from;
	public $to;
	public $start;
	public $scale;
	public $path;
	public $pattern;
	public $entry_path;
	public $exit_path;
	public $event;
	public $event_meta_key;
	public $language;
	public $country;
	public $city;
	public $referrer;
	public $referrer_name;
	public $os;
	public $browser;
	public $platform;
	public $screen_class;
	public $screen_height;
	public $screen_width;
	public $utm_source;
	public $utm_medium;
	public $utm_campaign;
	public $utm_content;
	public $utm_term;
	public $limit;
	public $include_avg_time_on_page;
}
