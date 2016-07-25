<?php

namespace Drupal\campaignion_logcrm;

class NewsletterComponentExporter {
  public function value($component, $values) {
    if ($component['extra']['display'] == 'radios') {
      if ($values['subscribed']) {
        return $component['extra']['radio_labels'][1];
      }
      else {
        return FALSE;
      }
    }
    else {
      if ($values['subscribed']) {
        return $component['extra']['checkbox_label'];
      }
      else {
        return FALSE;
      }
    }
  }
}
