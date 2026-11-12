<?php
declare(strict_types=1);

namespace Pirsch;

/**
 * Thrown when a request to the Pirsch API fails.
 */
class PirschException extends \Exception {
	private ?int $statusCode;

	public function __construct(string $message, ?int $statusCode = null, ?\Throwable $previous = null) {
		parent::__construct($message, $statusCode ?? 0, $previous);
		$this->statusCode = $statusCode;
	}

	public function getStatusCode(): ?int {
		return $this->statusCode;
	}
}
