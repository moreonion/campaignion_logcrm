<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_logcrm\WebformComponent\Verbatim;

/**
 * Class loader and dependency injection manager for campaignion_logcrm.
 */
class Loader {

  protected static $instance = NULL;
  protected $componentCache = [];
  protected $submissionExporter = NULL;

  protected $map = [];

  /**
   * Get or create the global instance of the loader.
   */
  public static function instance() {
    if (!static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
  }

  /**
   * Create a new loader instance.
   */
  public function __construct() {
    $this->map = [
      'select' => '\\Drupal\\campaignion_logcrm\\WebformComponent\\Select',
    ];
  }

  /**
   * Get a global submission exporter instance.
   */
  public function submissionExporter() {
    if (!$this->submissionExporter) {
      $this->submissionExporter = new SubmissionExporter($this);
    }
    return $this->submissionExporter;
  }

  /**
   * Get the component exporter for a webform component type.
   *
   * @param string $type
   *   Webform component type name.
   */
  public function componentExporter($type) {
    if (isset($this->componentCache[$type])) {
      return $this->componentCache[$type];
    }
    if (isset($this->map[$type])) {
      $class = $this->map[$type];
      $e = new $class($this);
    }
    else {
      $e = new Verbatim($this);
    }
    $this->componentCache[$type] = $e;
    return $e;
  }

}
