<?php

namespace Drupal\campaignion_logcrm;

use Drupal\wps_test_method\DummyController;
use Upal\DrupalUnitTestCase;

/**
 * Test the payment exporter service.
 */
class PaymentExporterTest extends DrupalUnitTestCase {

  /**
   * Test exporting a payment object with all data.
   */
  public function testExportPaymentFull() {
    $payment = entity_create('payment', [
      'pid' => 42,
      'currency_code' => 'EUR',
      'method' => entity_create('payment_method', [
        'title_specific' => 'Dummy method',
        'title_generic' => 'Test payment method',
        'controller' => new DummyController(),
      ]),
    ]);
    $payment->method->controller->name = 'controller_machine_name';
    $payment->setLineItem(new \PaymentLineItem([
      'name' => 'foo',
      'quantity' => 2,
      'amount' => 3.5,
    ]));
    $exporter = new PaymentExporter();
    $data = $exporter->toJson($payment);
    $this->assertEqual([
      'pid' => 42,
      'currency_code' => 'EUR',
      'total_amount' => 7 * 1.1,
      'total_amount_subunits' => 770,
      'status' => 'payment_status_new',
      'method_specific' => 'Dummy method',
      'method_generic' => 'Test payment method',
      'controller' => 'controller_machine_name',
    ], $data);
  }

}
