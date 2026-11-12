<?php
declare(strict_types=1);

namespace Pirsch;

const SCALE_DAY = 'day';
const SCALE_WEEK = 'week';
const SCALE_MONTH = 'month';
const SCALE_YEAR = 'year';

const CUSTOM_METRIC_TYPE_INTEGER = 'integer';
const CUSTOM_METRIC_TYPE_FLOAT = 'float';

/**
 * Filter for statistics requests. Fields set to null are not sent.
 */
class Filter {
	public const SCALE_DAY = 'day';
	public const SCALE_WEEK = 'week';
	public const SCALE_MONTH = 'month';
	public const SCALE_YEAR = 'year';

	public const CUSTOM_METRIC_TYPE_INTEGER = 'integer';
	public const CUSTOM_METRIC_TYPE_FLOAT = 'float';

	public ?string $id = null;
	public ?string $from = null;
	public ?string $to = null;
	public ?string $tz = null;
	public ?int $start = null;
	public ?string $scale = null;
	public ?string $path = null;
	public ?string $pattern = null;
	public ?string $entry_path = null;
	public ?string $exit_path = null;
	public ?string $event = null;
	public ?string $event_meta_key = null;
	public ?string $language = null;
	public ?string $country = null;
	public ?string $region = null;
	public ?string $city = null;
	public ?string $referrer = null;
	public ?string $referrer_name = null;
	public ?string $os = null;
	public ?string $browser = null;
	public ?string $platform = null;
	public ?string $screen_class = null;
	public ?string $utm_source = null;
	public ?string $utm_medium = null;
	public ?string $utm_campaign = null;
	public ?string $utm_content = null;
	public ?string $utm_term = null;
	public ?string $custom_metric_key = null;
	public ?string $custom_metric_type = null;
	public ?bool $include_avg_time_on_page = null;
	public ?int $offset = null;
	public ?int $limit = null;
	public ?string $sort = null;
	public ?string $direction = null;
	public ?string $search = null;
	public ?string $funnel_id = null;

	/**
	 * Event metadata filters, sent as meta_<key>=<value>.
	 *
	 * @var array<string, string>
	 */
	public array $meta = [];

	/**
	 * Tag filters, sent as tag_<key>=<value>.
	 *
	 * @var array<string, string>
	 */
	public array $tags = [];

	/**
	 * Returns the filter as query parameters.
	 *
	 * @return array<string, string|int>
	 */
	public function toQuery(): array {
		$query = [];

		foreach (get_object_vars($this) as $key => $value) {
			if ($key === 'meta' || $key === 'tags' || $value === null || $value === '') {
				continue;
			}

			$query[$key] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
		}

		foreach ($this->meta as $key => $value) {
			$query['meta_'.$key] = $value;
		}

		foreach ($this->tags as $key => $value) {
			$query['tag_'.$key] = $value;
		}

		return $query;
	}
}
