<?php

/**
 * @file
 * Hook implementations on behalf of the manual_direct_debit_uk module.
 */

use Drupal\manual_direct_debit_uk\AccountDataController;

/**
 * Implements hook_campaignion_logcrm_payment_event_data_alter().
 */
function manual_direct_debit_uk_campaignion_logcrm_payment_event_data_alter(array &$data, Payment $payment) {
  if ($payment->method->controller instanceof AccountDataController) {
    $md = $payment->method_data;
    $data['account'] = [
      'holder' => $md['holder'],
      'number' => $md['account'],
      'sort_code' => $md['bank_code'],
      'payment_date' => $md['payment_date'],
    ];
  }
}
