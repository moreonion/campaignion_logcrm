<?php

namespace Drupal\campaignion_logcrm;

class SelectComponentExporter {
  public function __construct() {
    module_load_include('inc', 'webform', 'components/select');
  }

  public function value($component, $values) {
    $options = _webform_select_options($component);
    $values = [];
    foreach ($values as $value) {
      if (isset($options[$value])) {
        $values[$value] = $options[$value];
      }
      else {
        // Select or other
        $values[$value] = $value;
      }
    }
    $single_value = (count($options) <= 1 && !$component['extra']['other_option']) || !$component['extra']['multiple'];
    if ($single_value) {
      $value = $values ? array_shift($values) : FALSE;
    }
    return $value;
  }
}
