<?php

/**
 * @file
 * Hook implementations on behalf othe paypal_payment module.
 */

use PayPalPaymentECPaymentMethodController as PayPalECController;
use PayPalPaymentPPSPaymentMethodController as PayPalPPSController;
use PayPalPaymentIPNController as PayPalIPNController;

/**
 * Implements hook_campaignion_logcrm_payment_event_data_alter().
 */
function paypal_payment_campaignion_logcrm_payment_event_data_alter(array &$data, Payment $payment) {
  $controller = $payment->method->controller;
  if ($controller instanceof PaypalECController || $controller instanceof PayPalPPSController) {
    $status = $payment->getStatus();
    $ipn = isset($status->ipn) ? $status->ipn : PayPalIPNController::load($status->psiid);
    if ($ipn) {
      $data['transaction_id'] = $ipn->txn_id;
    }
  }
}
