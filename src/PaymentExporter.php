<?php

namespace Drupal\campaignion_logcrm;

/**
 * Exporter service for payment data.
 */
class PaymentExporter {

  /**
   * Export a payment object to something we want to transmit in logCRM events.
   *
   * @param \Payment $payment
   *   The pament to export.
   *
   * @return array
   *   An associative array of data for this payment.
   */
  public function toJson(\Payment $payment) {
    $data['pid'] = $payment->pid;
    $status = $payment->getStatus();
    $data['currency_code'] = $payment->currency_code;
    $data['total_amount'] = $payment->totalAmount(TRUE);
    $data['status'] = $status->status;
    $data['method_specific'] = $payment->method->title_specific;
    $data['method_generic'] = $payment->method->title_generic;
    $controller = $payment->method->controller;
    $data['controller'] = $controller->name;
    if (webform_paymethod_select_implements_data_interface($controller)) {
      $data['payment_data'] = $controller->webformData($payment);
    }
    return $data;
  }

}
