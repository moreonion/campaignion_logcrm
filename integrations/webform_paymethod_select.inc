<?php

/**
 * @file
 * Hook implementation on behalf of the webform_paymethod_select module.
 */

/**
 * Implements hook_campaignion_logcrm_event_data_alter().
 */
function webform_paymethod_select_campaignion_logcrm_event_data_alter(array &$data, $type, array $context) {
  if ($type == 'form_submission') {
    $submission = $context['submission'];

    // Refresh payment objects. This seems to be necessary because the payment
    // happens in the same request and webform_paymethod_select doesn’t update
    // the data in place.
    $submission->payments = [];
    $submissions[$submission->sid] = $submission->unwrap();
    webform_paymethod_select_webform_submission_load($submissions);

    foreach (array_filter($submission->payments) as $cid => $payment) {
      $payment_data['pid'] = $payment->pid;
      $status = $payment->getStatus();
      $payment_data['currency_code'] = $payment->currency_code;
      $payment_data['total_amount'] = $payment->totalAmount(TRUE);
      $payment_data['status'] = $status->status;
      $payment_data['method_specific'] = $payment->method->title_specific;
      $payment_data['method_generic'] = $payment->method->title_generic;
      $payment_data['controller'] = $payment->method->controller->name;
      $data['_payments'][$cid] = $payment_data;
    }
  }
}
