<?php

namespace Drupal\campaignion_logcrm;

class Loader {
  protected static $instance = NULL;
  protected $componentCache = [];
  protected $submissionExporter = NULL;

  public static function instance() {
    if (!static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
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
    switch ($type) {
      case 'select':
        $e = new SelectComponentExporter();
        break;
      default:
        $e = new DefaultComponentExporter();
        break;
    }
    $this->componentCache[$type] = $e;
    return $e;
  }
}
