<?php

namespace Drupal\campaignion_logcrm;

/**
 * Exporter service for payment data.
 */
class PaymentExporter {

  /**
   * Map interval units used by payment_recurrence to ISO 8601 interval units.
   */
  const INTERVAL_UNITS = [
    'yearly' => 'Y',
    'monthly' => 'M',
    'daily' => 'D',
    'weekly' => 'W',
  ];

  /**
   * Submission exporter used to generate the action data.
   */
  protected $submissionExporter;

  /**
   * Create a new payment exporter.
   */
  public function __construct(SubmissionExporter $submission_exporter) {
    $this->submissionExporter = $submission_exporter;
  }

  /**
   * Generate the event data for a payment object.
   *
   * @param \Payment $payment
   *   The payment to export.
   *
   * @return array
   *   An associative array with the payment’s data.
   */
  public function paymentData(\Payment $payment) {
    $status = $payment->getStatus();
    $controller = $payment->method->controller;
    $data = [
      'pid' => $payment->pid,
      'currency_code' => $payment->currency_code,
      'total_amount' => $payment->totalAmount(TRUE),
      'status' => $status->status,
      'method_specific' => $payment->method->title_specific,
      'method_generic' => $payment->method->title_generic,
      'controller' => $controller->name,
      'line_items' => [],
    ];
    foreach ($payment->line_items as $item) {
      $data['line_items'][] = $this->lineItemData($item, $payment);
    }
    if (webform_paymethod_select_implements_data_interface($controller)) {
      $data['payment_data'] = $controller->webformData($payment);
    }

    // Let other modules alter the data.
    // Deprecated: Use campaignion_logcrm_event_data_alter() instead.
    drupal_alter('campaignion_logcrm_payment_event_data', $data, $payment);
    return $data;
  }

  /**
   * Generate data for a payment line item.
   */
  public function lineItemData(\PaymentLineItem $item, \Payment $payment) : array {
    $data = [
      'name' => $item->name,
      'amount' => (string) $item->amount,
      'quantity' => (string) $item->quantity,
      'tax_rate' => (string) $item->tax_rate,
      'recurrence_interval' => NULL,
    ];
    if (($recurrence = $item->recurrence ?? NULL) && $recurrence->interval_unit) {
      if ($unit = static::INTERVAL_UNITS[$recurrence->interval_unit] ?? NULL) {
        $factor = $recurrence->interval_value ?? 1;
        if ($factor > 0) {
          $data['recurrence_interval'] = "P{$factor}{$unit}";
        }
      }
      else {
        watchdog(
          'campaignion_logcrm',
          'Unknown recurrence interval unit (pid=%pid, name="%name"): %unit. Forwarding as one-off.',
          [
            '%unit' => $recurrence->interval_unit,
            '%pid' => $payment->pid,
            '%name' => $item->name
          ],
          WATCHDOG_ERROR,
        );
      }
    }
    return $data;
  }

  /**
   * Create a payment_success event from a payment object.
   *
   * @deprecated in 1.13 and will be removed in 2.x.
   *   Use the payment_status_change events instead.
   *
   * @param \Payment $payment
   *   The payment to export.
   *
   * @return Event
   *   A webhook event with data for the payment.
   */
  public function createSuccessEvent(\Payment $payment) {
    $data = $this->paymentData($payment);
    $submission = $payment->contextObj->getSubmission();
    $data['uuid'] = $submission->uuid;
    $data['action'] = $this->submissionExporter->actionData($submission);

    $context['payment'] = $payment;
    $context['submission'] = $submission;
    return Event::fromData('payment_success', $payment->getStatus()->created, $data, $context);
  }

  /**
   * Create a payment_status_change event.
   *
   * @param \Payment $payment
   *   The payment for which to report the status change.
   * @param \PaymentStatusItem $prev_status_item
   *   The previous status item.
   *
   * @return Event
   *   A logCRM event with data for the payment status change.
   */
  public function createStatusChangeEvent(\Payment $payment, \PaymentStatusItem $prev_status_item) {
    $data = $this->paymentData($payment);
    $status = $payment->getStatus();
    $data['previous_status'] = $prev_status_item->status;
    $submission = $payment->contextObj->getSubmission();
    $data['uuid'] = $submission->uuid;
    $context['payment'] = $payment;
    $context['submission'] = $submission;
    return Event::fromData('payment_status_change', $status->created, $data, $context);
  }

}
