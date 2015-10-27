<?php

namespace Drupal\campaignion_logcrm;

use \Drupal\little_helpers\Webform\Submission;

class SubmissionStub extends Submission {
  protected $data;
  public function __construct($node, $submission, $data) {
    parent::__construct($node, $submission);
    $this->data = $data;
  }
  public function valueByCid($cid) {
    return $this->data[$cid];
  }
}

class EventTest extends \DrupalUnitTestCase {
  public function test_fromSubmission() {
    $s = (object) [
      'uuid' => 'test-uuid',
      'submitted' => 1445948845,
      'node' => (object) [
        'uuid' => 'test-node-uuid',
        'title' => 'Test title',
        'webform' => ['components' => [
          1 => ['cid' => 1, 'form_key' => 'text'],
          2 => ['cid' => 2, 'form_key' => 'number'],
          3 => ['cid' => 3, 'form_key' => 'nothing'],
        ]],
      ],
    ];
    $data = [
      1 => 'TestText',
      2 => 57,
      3 => NULL,
    ];
    $submission = new SubmissionStub($s->node, $s, $data);

    $e = Event::fromSubmission($submission);
    $this->assertEquals([
      'text' => 'TestText',
      'number' => 57,
      'uuid' => 'test-uuid',
      'date' => '2015-10-27T13:27:25+01:00',
    ], $e->toArray());
  }

  public function test_fromPayment() {
    $method = (object) [
      'controller' => (object) ['name' => 'test controller'],
      'title_specific' => 'test specific',
      'title_generic' => 'test generic',
    ];
    $payment = $this->getMockBuilder('\Payment')
      ->setConstructorArgs([[
        'pid' => 1,
        'currency_code' => 'EUR',
        'method' => $method,
      ]])->getMock();
    $status = (object) [
      'created' => 1445948845,
      'status' => 'test success',
    ];
    $payment->method('getStatus')->willReturn($status);
    $payment->method('totalAmount')->willReturn(42);
    $payment->contextObj = $this->getMockBuilder('\Drupal\webform_paymethod_select\WebformPaymentContext')
      ->disableOriginalConstructor()
      ->getMock();
    $submission = (object) [
      'uuid' => 'test-submission-uuid',
      'node' => (object) ['uuid' => 'test-node-uuid', 'title' => 'Test node'],
    ];
    $payment->contextObj->method('getSubmission')->willReturn($submission);

    $e = Event::fromPayment($payment);
    $this->assertEquals([
      'uuid' => 'test-submission-uuid',
      'action' => [
        'uuid' => 'test-node-uuid',
        'title' => 'Test node',
      ],
      'pid' => 1,
      'currency_code' => 'EUR',
      'total_amount' => 42,
      'status' => 'test success',
      'method_specific' => 'test specific',
      'method_generic' => 'test generic',
      'controller' => 'test controller',
      'date' => '2015-10-27T13:27:25+01:00',
    ], $e->toArray());
  }
}
