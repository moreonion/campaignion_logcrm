<?php

namespace Drupal\campaignion_logcrm;

class DefaultComponentExporter {
  public function value($component, array $values) {
    if ($values) {
      reset($values);
      return current($values);
    }
  }
}
