<?php

namespace Drupal\campaignion_logcrm\WebformComponent;

use Drupal\little_helpers\Webform\Submission;

/**
 * Component exportor that uses the first value as provided by webform.
 */
class Verbatim {

  protected $loader;

  /**
   * Create a new instance.
   */
  public function __construct($loader) {
    $this->loader = $loader;
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
