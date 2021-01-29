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
    $currency = currency_load($payment->currency_code);
    $subunits = $currency->subunits ?: 1;
    $data['pid'] = $payment->pid;
    $status = $payment->getStatus();
    $data['currency_code'] = $payment->currency_code;
    $data['total_amount'] = $payment->totalAmount(TRUE);
    $data['total_amount_subunits'] = (int) round($payment->totalAmount(TRUE) * $subunits);
    $data['status'] = $status->status;
    $data['method_specific'] = $payment->method->title_specific;
    $data['method_generic'] = $payment->method->title_generic;
    $data['controller'] = $payment->method->controller->name;
    $data['line_items'] = [];
    foreach ($payment->line_items as $name => $line_item) {
      $data['line_items'][$name] = [
        'unit_amount_subunits' => (int) round($line_item->amount * $subunits),
        'quantity' => $line_item->quantity,
        'tax_rate' => $line_item->tax_rate,
        'net_amount_subunits' => (int) round($line_item->totalAmount(FALSE) * $subunits),
        'total_amount_subunits' => (int) round($line_item->totalAmount(TRUE) * $subunits),
        'description' => format_string($line_item->description, $line_item->description_arguments),
      ];
    }
    return $data;
  }

}
