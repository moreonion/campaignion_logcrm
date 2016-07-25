<?php

namespace Drupal\campaignion_logcrm;

class Loader {
  protected static $instance = NULL;
  protected $componentCache = [];
  protected $submissionExporter = NULL;

  protected $map = [];

  public static function instance() {
    if (!static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
  }

  public function __construct() {
    $this->map = [
      'newsletter' => '\\Drupal\\campaignion_logcrm\\NewsletterComponentExporter',
      'select' => '\\Drupal\\campaignion_logcrm\\SelectComponentExporter',
    ];
  }

  public function submissionExporter() {
    if (!$this->submissionExporter) {
      $this->submissionExporter = new SubmissionExporter($this);
    }
    return $this->submissionExporter;
  }

  public function componentExporter($type) {
    if (isset($this->componentCache[$type])) {
      return $this->componentCache[$type];
    }
    if (isset($this->map[$type])) {
      $class = $this->map[$type];
      $e = new $class();
    }
    else {
      $e = new DefaultComponentExporter();
    }
    $this->componentCache[$type] = $e;
    return $e;
  }
}
