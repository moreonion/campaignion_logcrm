<?php

namespace Drupal\campaignion_logcrm;

class NewsletterComponentExporterTest extends \DrupalUnitTestCase {

  protected $radio = [
    'type' => 'newsletter',
    'form_key' => 'newsletter',
    'extra' => [
      'display' => 'radios',
      'checkbox_label' => 'Wrong Yes',
      'radio_labels' => ['No', 'Yes'],
    ],
  ];
  protected $checkbox = [
    'type' => 'newsletter',
    'form_key' => 'newsletter',
    'extra' => [
      'display' => 'checkbox',
      'checkbox_label' => 'Yes',
      'radio_labels' => ['Wrong No', 'Wrong Yes'],
    ],
  ];

  public function setUp() {
    $this->e = new NewsletterComponentExporter();
  }

  public function test_radio_yes_returnsLabel() {
    $this->assertEqual('Yes', $this->e->value($this->radio, ['radios:opt-in']));
  }

  public function test_radio_no_returnsFalse() {
    $this->assertEqual(FALSE ,$this->e->value($this->radio, ['radios:no-change']));
  }

  public function test_checkbox_yes_returnsLabel() {
    $this->assertEqual('Yes', $this->e->value($this->checkbox, ['checkbox:opt-in']));
  }

  public function test_checkbox_no_returnsFalse() {
    $this->assertEqual(FALSE, $this->e->value($this->checkbox, ['checkbox:no-change']));
  }

}
