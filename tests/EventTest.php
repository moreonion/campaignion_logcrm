<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Webform\Submission;

/**
 * Test creating and dumping events.
 */
class EventTest extends \DrupalUnitTestCase {

  /**
   * Create a test submission.
   */
  public function setUp() {
    parent::setUp();
    $s = (object) [
      'sid' => 12,
      'is_draft' => 0,
      'uuid' => 'test-uuid',
      'submitted' => 1445948845,
      'node' => (object) [
        'nid' => 1,
        'uuid' => 'test-node-uuid',
        'title' => 'Test title',
        'type' => 'node_type',
        'webform' => [],
      ],
      'tracking' => (object) [
        'tags' => [],
      ],
    ];
    $s->node->webform['components'] = [
      1 => ['cid' => 1, 'type' => 'text', 'form_key' => 'text'],
      2 => ['cid' => 2, 'type' => 'number', 'form_key' => 'number'],
      3 => ['cid' => 3, 'type' => 'hidden', 'form_key' => 'nothing'],
    ];
    $this->submission = new Submission($s->node, $s, []);
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
    node_type_get_name($submission->node);
    $submission->data = [
      1 => ['TestText'],
      2 => [57],
      3 => [NULL],
    ];

    $e = Event::fromSubmission($submission);
    $a = $e->toArray();
    unset($a['date']);
    $nid = $submission->node->nid;
    $link_options = ['absolute' => TRUE, 'alias' => TRUE];
    $this->assertEquals([
      'is_draft' => FALSE,
      'text' => 'TestText',
      'number' => 57,
      'uuid' => 'test-uuid',
      'type' => 'form_submission',
      'action' => [
        'uuid' => 'test-node-uuid',
        'title' => 'Test title',
        'needs_confirmation' => FALSE,
        'type' => 'node_type',
        'type_title' => FALSE,
      ],
      'tracking' => (object) [
        'tags' => [],
      ],
      '_links' => [
        'action' => url("node/$nid", $link_options),
        'submission' => url("node/$nid/submission/{$submission->sid}", $link_options),
      ],
    ], $a);
  }

  /**
   * Test creating a payment event.
   */
  public function testFromPayment() {
    $method = (object) [
      'controller' => (object) ['name' => 'test controller'],
      'title_specific' => 'test specific',
      'title_generic' => 'test generic',
    ];
    $payment = $this->getMockBuilder('\Payment')
      ->setConstructorArgs([
        ['pid' => 1, 'currency_code' => 'EUR', 'method' => $method],
      ])->getMock();
    $status = (object) [
      'created' => 1445948845,
      'status' => 'test success',
    ];
    $payment->method('getStatus')->willReturn($status);
    $payment->method('totalAmount')->willReturn(42);
    $payment->contextObj = $this->getMockBuilder('\Drupal\webform_paymethod_select\WebformPaymentContext')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->contextObj->method('getSubmission')->willReturn($this->submission);

    $e = Event::fromPayment($payment);
    $a = $e->toArray();
    unset($a['date']);
    $this->assertEquals([
      'uuid' => 'test-uuid',
      'type' => 'payment_success',
      'action' => [
        'uuid' => 'test-node-uuid',
        'title' => 'Test title',
        'type' => 'node_type',
        'type_title' => FALSE,
        'needs_confirmation' => FALSE,
      ],
      'pid' => 1,
      'currency_code' => 'EUR',
      'total_amount' => 42,
      'status' => 'test success',
      'method_specific' => 'test specific',
      'method_generic' => 'test generic',
      'controller' => 'test controller',
    ], $a);
  }

}
