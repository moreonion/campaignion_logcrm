<?php

namespace Drupal\campaignion_logcrm;

use Upal\DrupalUnitTestCase;

/**
 * Test hooks called during the submission life cycle.
 */
class SubmissionIntegrationTest extends DrupalUnitTestCase {

  /**
   * Set up test node.
   */
  public function setUp() : void {
    parent::setUp();
    require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';
    require_once drupal_get_path('module', 'webform') . '/includes/webform.submissions.inc';
    $node = (object) [
      'type' => 'webform',
      'title' => 'Form submission payment test',
    ];
    node_object_prepare($node);
    $node->webform['components'][1] = [
      'type' => 'email',
      'form_key' => 'email',
    ];
    foreach ($node->webform['components'] as $cid => &$component) {
      webform_component_defaults($component);
      $component += [
        'pid' => 0,
        'cid' => $cid,
        'name' => $component['form_key'],
        'weight' => 0,
      ];
    }
    node_save($node);
    $this->node = $node;
  }

  /**
   * Delete test node and payment.
   */
  public function tearDown() : void {
    db_delete('campaignion_logcrm_queue')->execute();
    node_delete($this->node->nid);
    parent::tearDown();
  }

  /**
   * Test creating a new draft submission and then confirming it.
   */
  public function testSubmissionInsertAndConfirm() {
    $form_state['values']['submitted'][1] = ['test@example.com'];
    $submission = webform_submission_create($this->node, drupal_anonymous_user(), $form_state);
    webform_submission_insert($this->node, $submission);
    $items = db_select('campaignion_logcrm_queue', 'q')->fields('q')
      ->execute()->fetchAll();
    $this->assertEqual(1, count($items));

    campaignion_logcrm_webform_confirm_email_email_confirmed($this->node, $submission);
    $items = db_select('campaignion_logcrm_queue', 'q')->fields('q')
      ->execute()->fetchAll();
    $this->assertEqual(2, count($items));
  }

}
