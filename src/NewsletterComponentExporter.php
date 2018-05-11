<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_newsletters\ValuePrefix;

class NewsletterComponentExporter {

  public function value($component, $values) {
    $opt_in = ValuePrefix::remove($values) == 'opt-in';
    if ($component['extra']['display'] == 'radios') {
      return $opt_in ? $component['extra']['radio_labels'][1] : FALSE;
    }
    else {
      return $opt_in ? $component['extra']['checkbox_label'] : FALSE;
    }
  }

}
