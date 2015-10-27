<?php

namespace Drupal\campaignion_logcrm;

class ApiError extends \RuntimeException {
  public function __construct($code, $status, $error) {
    $this->variables = [
      '@code' => $code,
      '@status' => $status,
      '@error' => $error,
    ];
    parent::__construct('LogCRM-API error @code @status: @error', $code);
  }
  public function log() {
    \watchdog('campaignion_logcrm', $this->message, $this->variables, WATCHDOG_ERROR);
  }
}
