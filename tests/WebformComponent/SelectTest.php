<?php

namespace Drupal\campaignion_logcrm\WebformComponent;

use Upal\DrupalUnitTestCase;

/**
 * Test the select component exporter.
 */
class SelectTest extends DrupalUnitTestCase {

  protected $radio = [
    'type' => 'select',
    'form_key' => 'radio',
    'extra' => [
      'items' => "1|o1\n2|o2",
      'options_source' => '',
      'multiple' => FALSE,
    ],
  ];
  protected $checkbox = [
    'type' => 'select',
    'form_key' => 'checkbox',
    'extra' => [
      'items' => "1|o1\n2|o2",
      'options_source' => '',
      'multiple' => TRUE,
    ],
  ];
  protected $checkboxSingle = [
    'type' => 'select',
    'form_key' => 'checkbox',
    'extra' => [
      'items' => "1|o1",
      'options_source' => '',
      'multiple' => TRUE,
      'other_option' => '',
    ],
  ];
  protected $checkboxPrebuilt = [
    'type' => 'select',
    'form_key' => 'checkbox',
    'extra' => [
      'items' => '',
      'options_source' => 'days',
      'multiple' => TRUE,
      'other_option' => '',
    ],
  ];

  /**
   * Create test node and component exporter.
   */
  public function setUp() {
    parent::setUp();
    $this->node = (object) [
      'type' => 'webform',
      'title' => 'Select component exporter test',
    ];
    node_save($this->node);
    $this->radio += ['nid' => $this->node->nid];
    $this->checkbox += ['nid' => $this->node->nid];
    $this->checkboxSingle += ['nid' => $this->node->nid];
    $this->checkboxPrebuilt += ['nid' => $this->node->nid];
    $this->e = new Select();
  }

  /**
   * Delete the test node.
   */
  public function tearDown() {
    node_delete($this->node->nid);
    parent::tearDown();
  }

  public function test_radio_withValue_returnsArray() {
    $this->assertEqual(['1' => 'o1'], $this->e->filter($this->radio, ['1']));
  }

  public function test_radio_noValue_returnsFalse() {
    $this->assertTrue(FALSE === $this->e->filter($this->radio, ['']));
  }

  public function test_checkbox_twoValues_returnsArray() {
    $this->assertEqual([
      '1' => 'o1',
      '2' => 'o2',
    ], $this->e->filter($this->checkbox, ['1', '2']));
  }

  public function test_checkbox_oneValue_returnsArray() {
    $this->assertEqual([
      '1' => 'o1',
    ], $this->e->filter($this->checkbox, ['1']));
  }

  public function test_checkbox_noValue_returnsEmptyArray() {
    $this->assertEqual([], $this->e->filter($this->checkbox, [NULL]));
  }

  public function test_checkbox_singleOption_selected_returnsLabel() {
    $this->assertEqual('o1', $this->e->filter($this->checkboxSingle, ['1']));
  }

  public function test_checkbox_singleOption_notSelected_returnsFalse() {
    $this->assertTrue(FALSE === $this->e->filter($this->checkboxSingle, [NULL]));
  }

  public function test_checkbox_prebuilt_twoSelected_returnsArray() {
    $this->assertEqual([
      'sunday' => 'Sunday',
      'monday' => 'Monday',
    ], $this->e->filter($this->checkboxPrebuilt, ['sunday', 'monday']));
  }
}
