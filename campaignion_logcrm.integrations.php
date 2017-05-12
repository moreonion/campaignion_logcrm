<?php

/**
 * @file
 *
 * Hook implementations on behalf of other modules.
 */

use \Drupal\manual_direct_debit_uk\AccountDataController;
use \PayPalPaymentECPaymentMethodController as PayPalECController;
use \PayPalPaymentPPSPaymentMethodController as PayPalPPSController;
use \PayPalPaymentIPNController as PayPalIPNController;

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

/**
 * Implements hook_campaignion_logcrm_payment_event_data_alter().
 */
function paypal_payment_campaignion_logcrm_payment_event_data_alter(array &$data, Payment $payment) {
  $controller = $payment->method->controller;
  if ($controller instanceof PaypalECController || $controller instanceof PayPalPPSController) {
    $status = $payment->getStatus();
    if ($ipn = PayPalIPNController::load($status->psiid)) {
      $data['transaction_id'] = $ipn->txn_id;
    }
  }
}
