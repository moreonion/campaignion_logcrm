<?php

namespace Drupal\campaignion_logcrm;

/**
 * Exporter service for payment data.
 */
class PaymentExporter {

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
    ];
    if (webform_paymethod_select_implements_data_interface($controller)) {
      $data['payment_data'] = $controller->webformData($payment);
    }

    // Let other modules alter the data.
    // Deprecated: Use campaignion_logcrm_event_data_alter() instead.
    drupal_alter('campaignion_logcrm_payment_event_data', $data, $payment);
    return $data;
  }

  /**
   * Create a payment_success event from a payment object.
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
    $data['status_old'] = $prev_status_item->status;
    $data['status_new'] = $status->status;
    $submission = $payment->contextObj->getSubmission();
    $data['uuid'] = $submission->uuid;
    $context['payment'] = $payment;
    $context['submission'] = $submission;
    return Event::fromData('payment_status_change', $status->created, $data, $context);
  }

}
