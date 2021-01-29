<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Webform\Submission;
use Upal\DrupalUnitTestCase;

/**
 * Test submitting a webform with a payment.
 */
class FormSubmissionPaymentTest extends DrupalUnitTestCase {

  /**
   * Create test node and payment.
   */
  public function setUp() : void {
    parent::setUp();

    $controller = payment_method_controller_load(wps_test_method_payment_method_controller_info()[0]);
    $method = entity_create('payment_method', ['controller' => $controller]);
    entity_save('payment_method', $method);
    $payment = entity_create('payment', ['method' => $method]);
    $payment->setLineItem(new \PaymentLineItem(['amount' => 3]));
    entity_save('payment', $payment);
    $this->payment = $payment;

    $node = (object) [
      'type' => 'webform',
      'title' => 'Form submission payment test',
    ];
    node_object_prepare($node);
    $node->webform['components'][1] = [
      'cid' => 1,
      'type' => 'paymethod_select',
      'pid' => 0,
      'form_key' => 'paymethod_select',
      'name' => 'Pay',
      'weight' => 0,
      'extra' => [
        'selected_payment_methods' => [$method->pmid => $method->pmid],
      ],
    ];
    $node->webform['components'][2] = [
      'cid' => 2,
      'form_key' => 'paymethod_select2',
    ] + $node->webform['components'][1];
    node_save($node);
    $this->node = $node;
  }

  /**
   * Delete test node and payment.
   */
  public function tearDown() : void {
    node_delete($this->node->nid);
    entity_delete('payment_method', $this->payment->method->pmid);
    entity_delete('payment', $this->payment->pid);
    parent::tearDown();
  }

  /**
   * Test submission with one payment.
   */
  public function testSubmissionPaymentData() {
    $submission = new Submission($this->node, (object) [
      'sid' => 2,
      'nid' => $this->node->nid,
      'submitted' => 3,
      'uuid' => 'test-submission-uuid',
      'is_draft' => FALSE,
      'data' => [1 => [$this->payment->pid], 2 => []],
    ]);
    $e = Event::fromSubmission($submission);
    $a = $e->toArray();
    $this->assertArrayHasKey('_payments', $a);
    $payment_data = $a['_payments'][1];
    $this->assertEquals([
      'pid' => (int) $this->payment->pid,
      'currency_code' => 'XXX',
      'total_amount' => 3.0,
      'status' => 'payment_status_new',
      'method_specific' => '',
      'method_generic' => '',
      'controller' => '\\Drupal\\wps_test_method\\DummyController',
    ], $payment_data);
  }

}
