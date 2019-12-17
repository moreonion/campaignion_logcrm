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
      static::$instance = new static(static::getPluginInfo());
    }
    return static::$instance;
  }

  /**
   * Invoke hooks to get the component exporter plugin info.
   *
   * @return array
   *   Component exporter plugin info.
   */
  public static function getPluginInfo() {
    $info = module_invoke_all('campaignion_logcrm_webform_component_exporter_info');
    drupal_alter('campaignion_logcrm_webform_component_exporter_info', $info);
    foreach ($info as &$plugin) {
      if (is_string($plugin)) {
        $plugin = ['class' => $plugin];
      }
    }
    return $info;
  }

  /**
   * Create a new loader instance.
   */
  public function __construct(array $plugin_info) {
    $this->map = $plugin_info;
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
      $info = $this->map[$type];
      $class = $info['class'];
      $e = $class::fromConfig($info, $this);
    }
    else {
      $e = Verbatim::fromConfig([], $this);
    }
    $this->componentCache[$type] = $e;
    return $e;
  }

}
