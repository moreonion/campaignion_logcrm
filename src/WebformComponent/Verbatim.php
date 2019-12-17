<?php

namespace Drupal\campaignion_logcrm\WebformComponent;

use Drupal\campaignion_logcrm\Loader;
use Drupal\little_helpers\Webform\Submission;

/**
 * Component exportor that uses the first value as provided by webform.
 */
class Verbatim {

  /**
   * Create new instance by passing the config.
   *
   * @param array $config
   *   The plugin configuration.
   * @param \Drupal\campaignion_logcrm\Loader $loader
   *   The plugin loader.
   */
  public static function fromConfig(array $config, Loader $loader) {
    return new static();
  }

  /**
   * Return the first value of the webform component (if any).
   */
  public function filter(array $component, array $values) {
    if ($values) {
      reset($values);
      return current($values);
    }
  }

  /**
   * Read the value from the submission.
   */
  public function value(array $component, Submission $submission) {
    if ($values = $submission->valuesByCid($component['cid'])) {
      return $this->filter($component, $values);
    }
    return NULL;
  }

}
