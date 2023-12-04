<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Services\Container;
use Drupal\webform_paymethod_select\WebformPaymentContext;
use Drupal\wps_test_method\DummyController;
use Upal\DrupalUnitTestCase;

/**
 * Test module-level hook implementations.
 */
class ModuleTest extends DrupalUnitTestCase {

  /**
   * Set up mock services.
   */
  public function setUp() : void {
    parent::setUp();
    $container = Container::get();
    $this->paymentExporter = $this->createMock(PaymentExporter::class);
    $container->inject('campaignion_logcrm.payment_exporter', $this->paymentExporter);
    $this->queue = $this->createMock(Queue::class);
    $container->inject('campaignion_logcrm.queue', $this->queue);
  }

  /**
   * Remove mocked services and delete the test payment.
   */
  public function tearDown() : void {
    drupal_static_reset(Container::class);
    if ($this->payment->pid ?? NULL) {
      entity_delete('payment', $this->payment->pid);
    }
    parent::tearDown();
  }

  /**
   * Test a payment going through a few status changes.
   */
  public function testPaymentStatusChange() {
    $this->payment = $payment = entity_create('payment', [
      'pid' => 42,
      'currency_code' => 'EUR',
      'method' => entity_create('payment_method', [
        'title_specific' => 'Dummy method',
        'title_generic' => 'Test payment method',
        'controller' => new DummyController(),
      ]),
    ]);
    entity_save('payment', $payment);
    $payment->contextObj = $this->createMock(WebformPaymentContext::class);
    $payment->contextObj->method('toContextData')->willReturn([]);
    $success_event = new Event('payment_success');
    $this->paymentExporter->method('createSuccessEvent')->willReturn($success_event);
    $change_event = new Event('payment_status_change');
    $this->paymentExporter->method('createStatusChangeEvent')->willReturn($change_event);
    // Expect one call from the payment status change.
    $this->queue->expects($this->at(0))->method('addItem')
      ->with('payment', $payment->pid, $success_event);
    // Expect one call from the entity_save() for the success status.
    $predicted_success_psiid = $payment->getStatus()->psiid + 2;
    $this->queue->expects($this->at(1))->method('addItem')
      ->with('payment_status_item', $predicted_success_psiid, $change_event);
    $payment->method->controller->name = 'controller_machine_name';
    $payment->setLineItem(new \PaymentLineItem([
      'name' => 'foo',
      'quantity' => 2,
      'amount' => 3.5,
    ]));
    // These should not trigger anything.
    $payment->setStatus(new \PaymentStatusItem(PAYMENT_STATUS_PENDING));
    entity_save('payment', $payment);
    // Trigger payment_success event via hook_payment_status_change().
    $payment->setStatus(new \PaymentStatusItem(PAYMENT_STATUS_SUCCESS));
    // Trigger payment_status_change event through the entity save.
    entity_save('payment', $payment);
  }

}
