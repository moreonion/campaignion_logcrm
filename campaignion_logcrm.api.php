<?php

/**
 * @file
 * Document hooks invoked by this module.
 *
 * Code in this file is only for documentation purposes. It's never executed.
 */

/**
 * Register webform component exporter plugins.
 *
 * @return array
 *   A map of webform component types to configuration arrays. Each item is
 *   is either a class name of a plugin or an array. If it is an array it must
 *   have the following keys:
 *   - class: The class name of the plugin.
 */
function hook_campaignion_logcrm_webform_component_exporter_info() {
  $info['custom'] = '\\Drupal\\custom_module\\Exporter';
  return $info;
}

/**
 * Alter the webform component exporter plugin info.
 *
 * @param array $info
 *   The component plugin info.
 *
 * @see hook_campaignion_logcrm_webform_component_exporter_info()
 */
function hook_campaignion_logcrm_webform_component_exporter_info_alter(array &$info) {
  $info['custom'] = [
    'class' => '\\Drupal\\other_module\\Exporter',
    'param1' => 'value',
  ];
}

/**
 * Allow other modules to modify the event data.
 *
 * @param array $data
 *   Original event data array.
 * @param string $type
 *   The event type.
 * @param array $context
 *   Array of objects used to generate the event data.
 */
function hook_campaignion_logcrm_payment_event_data_alter(array &$data, $type, array $context) {
  if (isset($context['submission'])) {
    $link_options = ['absolute' => TRUE, 'alias' => TRUE];
    $data['_link']['edit'] = url("node/{$submission->node->nid}/submission/{$submission->sid}/edit", $link_options);
  }
}

/**
 * Allow other modules to modify the event data for payment events.
 *
 * @param array $data
 *   Original event data array.
 * @param \Payment $payment
 *   The payment object for which we do generate data.
 */
function hook_campaignion_logcrm_payment_event_data_alter(array &$data, Payment $payment) {
  $data['tax'] = $payment->totalAmount(TRUE) - $payment->totalAmount(FALSE);
}
