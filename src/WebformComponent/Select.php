<?php

namespace Drupal\campaignion_logcrm\WebformComponent;

/**
 * Exporter for select components.
 */
class Select extends Verbatim {

  /**
   * Load all needed includes.
   */
  public function __construct() {
    module_load_include('inc', 'webform', 'components/select');
  }

  /**
   * Filter the component values.
   *
   * @param array $component
   *   The webform component configuration.
   * @param array $values
   *   All the values for the webform component.
   *
   * @return mixed
   *   Output data for logCRM in an JSON encodable format.
   */
  public function filter(array $component, array $values) {
    $options = _webform_select_options($component);

    // Create a associative array with all selected values and their labels.
    $new_values = [];
    foreach ($values as $value) {
      if (is_null($value)) {
        // Remove NULL values -> bug in little_helpers.
        continue;
      }
      if (isset($options[$value])) {
        $new_values[$value] = $options[$value];
      }
      else {
        if (!$component['extra']['multiple'] && $value === '') {
          // Radio without value so don't pass any.
        }
        else {
          // Select or other.
          $new_values[$value] = $value;
        }
      }
    }

    // Allow checkboxes to have just their label as value.
    $single_value = count($options) <= 1 && !$component['extra']['other_option'] && $component['extra']['multiple'];
    if ($single_value) {
      return $new_values ? array_shift($new_values) : FALSE;
    }
    // Radio without selection.
    elseif (!$component['extra']['multiple'] && !$new_values) {
      return FALSE;
    }
    else {
      return $new_values;
    }
  }

}
