<?php

namespace Drupal\campaignion_logcrm\WebformComponent;

/**
 * Component exportor that uses the first value as provided by webform.
 */
class Verbatim {

  /**
   * Return the first value of the webform component (if any).
   */
  public function value(array $component, array $values) {
    if ($values) {
      reset($values);
      return current($values);
    }
  }

}
