<?php

namespace Drupal\campaignion_logcrm;

use Drupal\manual_direct_debit_uk\AccountDataController;
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
      'total_amount' => 7.0,
      'status' => 'payment_status_new',
      'method_specific' => 'Dummy method',
      'method_generic' => 'Test payment method',
      'controller' => 'controller_machine_name',
    ], $data);
  }

  /**
   * Test exporting payment data of a manual_direct_debit_uk payment.
   */
  public function testExportManualDirectDebit() {
    $payment = entity_create('payment', [
      'pid' => 42,
      'currency_code' => 'EUR',
      'method' => entity_create('payment_method', [
        'title_specific' => 'Dummy method',
        'title_generic' => 'Test payment method',
        'controller' => new AccountDataController(),
      ]),
    ]);
    $payment->method->controller->name = '\\Drupal\\manual_direct_debit_uk\\AccountDataController';
    $payment->setLineItem(new \PaymentLineItem([
      'name' => 'foo',
      'quantity' => 2,
      'amount' => 3.5,
    ]));
    $payment->method_data = [
      'holder' => 'Account holder name',
      'country' => 'GB',
      'iban' => 'no-iban',
      'bic' => 'no-bic',
      'account' => '31926819',
      'bank_code' => '601613',
      'payment_date' => '15',
    ];
    $exporter = new PaymentExporter();
    $data = $exporter->toJson($payment);
    drupal_alter('campaignion_logcrm_payment_event_data', $data, $payment);
    $this->assertEqual([
      'pid' => 42,
      'currency_code' => 'EUR',
      'total_amount' => 7.0,
      'status' => 'payment_status_new',
      'method_specific' => 'Dummy method',
      'method_generic' => 'Test payment method',
      'controller' => '\\Drupal\\manual_direct_debit_uk\\AccountDataController',
      // Implementation in PaymentExporter based $controller->webformData().
      'payment_data' => [
        'account_holder' => 'Account holder name',
        'account_country' => 'GB',
        'account_iban' => 'no-iban',
        'account_bic' => 'no-bic',
        'account_number' => '31926819',
        'account_bank_code' => '601613',
        'account_payment_date' => '15',
      ],
      // From hook_campaignion_logcrm_payment_event_data_alter().
      'account' => [
        'holder' => 'Account holder name',
        'number' => '31926819',
        'sort_code' => '601613',
        'payment_date' => '15',
      ],
    ], $data);
  }

}
