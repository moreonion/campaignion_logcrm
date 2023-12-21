<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_logcrm\Tests\MockSubmission;
use Drupal\little_helpers\Webform\Submission;

require_once drupal_get_path('module', 'webform') . '/includes/webform.components.inc';

/**
 * Test creating and dumping events.
 */
class EventTest extends \DrupalUnitTestCase {

  /**
   * Create test node and submission.
   */
  public function setUp() : void {
    parent::setUp();

    $node = (object) [
      'type' => 'webform',
      'title' => 'Form submission test',
    ];
    node_object_prepare($node);
    $node->webform['components'] = [
      1 => ['type' => 'text', 'form_key' => 'text'],
      2 => ['type' => 'number', 'form_key' => 'number'],
      3 => ['type' => 'hidden', 'form_key' => 'nothing'],
      4 => [
        'type' => 'opt_in',
        'form_key' => 'email_opt_in',
        'extra' => [
          'channel' => 'email',
        ],
      ],
      5 => ['type' => 'email', 'form_key' => 'email'],
    ];
    MockSubmission::prepareComponents($node);
    node_save($node);
    $this->node = $node;

    $s = (object) [
      'nid' => $node->nid,
      'sid' => 12,
      'is_draft' => 0,
      'uuid' => 'test-uuid',
      'submitted' => 1445948845,
      'completed' => 1445948846,
      'tracking' => (object) [
        'tags' => [],
      ],
    ];
    $submissions = [$s->sid => $s];
    campaignion_opt_in_webform_submission_load($submissions);
    $this->assertNotEmpty($s->opt_in);
    $this->submission = new Submission($node, $s);
  }

  /**
   * Delete test node and payment.
   */
  public function tearDown() : void {
    node_delete($this->node->nid);
    parent::tearDown();
  }

  /**
   * Test creating a submission confirmation event.
   */
  public function testFromSubmissionConfirmation() {
    $d = Event::fromSubmissionConfirmation($this->submission)->toArray();
    unset($d['date']);
    $this->assertEquals([
      'type' => 'form_submission_confirmed',
      'uuid' => 'test-uuid',
    ], $d);
  }

  /**
   * Test creating a submission event.
   */
  public function testFromSubmission() {
    $submission = $this->submission;
    $submission->data = [
      1 => ['TestText'],
      2 => [57],
      3 => [NULL],
      4 => ['radios:opt-in'],
      5 => ['test@example.com'],
    ];

    $e = Event::fromSubmission($submission);
    $a = $e->toArray();
    unset($a['date']);
    $nid = $submission->node->nid;
    $link_options = ['absolute' => TRUE, 'alias' => FALSE];
    $this->assertEquals([
      'is_draft' => FALSE,
      'text' => 'TestText',
      'number' => 57,
      'email' => 'test@example.com',
      'email_opt_in' => 'radios:opt-in',
      'uuid' => 'test-uuid',
      'type' => 'form_submission',
      'action' => [
        'uuid' => $this->node->uuid,
        'title' => $this->node->title,
        'needs_confirmation' => FALSE,
        'type' => 'webform',
        'type_title' => 'Webform',
        'tags' => [],
        'source_tags' => [],
        'campaign_tags' => [],
      ],
      'tracking' => (object) [
        'tags' => [],
      ],
      '_links' => [
        'action' => url("node/$nid", $link_options),
        'action_pretty_url' => url("node/$nid", ['alias' => TRUE] + $link_options),
        'submission' => url("node/$nid/submission/{$submission->sid}", $link_options),
      ],
      '_optins' => [
        4 => [
          'address' => 'test@example.com',
          'operation' => 'opt-in',
          'value' => 'radios:opt-in',
          'channel' => 'email',
          'statement' => '',
          'unsubscribe_all' => TRUE,
          'unsubscribe_unknown' => FALSE,
          'trigger_opt_in_email' => FALSE,
          'trigger_welcome_email' => FALSE,
          'lists' => [],
          'ip_address' => '127.0.0.1',
        ],
      ],
      '_submitted_at' => '2015-10-27T12:27:25+0000',
      '_completed_at' => '2015-10-27T12:27:26+0000',
    ], $a);
  }

}
