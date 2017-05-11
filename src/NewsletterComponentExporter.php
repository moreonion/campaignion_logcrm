<?php

namespace Drupal\campaignion_logcrm;

class NewsletterComponentExporter {
  public function value($component, $values) {
    if ($component['extra']['display'] == 'radios') {
      if (reset($values)) {
        return $component['extra']['radio_labels'][1];
      }
      else {
        return FALSE;
      }
    }
    else {
      if (reset($values)) {
        return $component['extra']['checkbox_label'];
      }
      else {
        return FALSE;
      }
    }
  }
}
