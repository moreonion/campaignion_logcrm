<?php

/**
 * @file
 * Hook implementation on behalf of the webform_paymethod_select module.
 */

use Drupal\little_helpers\Services\Container;

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

    $exporter = Container::get()->loadService('campaignion_logcrm.payment_exporter');
    foreach (array_filter($submission->payments) as $cid => $payment) {
      $data['_payments'][$cid] = $exporter->paymentData($payment);
    }
  }
}
