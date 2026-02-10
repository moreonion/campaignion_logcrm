<?php

/**
 * @file
 * Information about configuration variables defined by this module.
 */

/**
 * Implements hook_variable_info().
 */
function campaignion_logcrm_variable_info($options) {
  $v['campaignion_logcrm_organization'] = [
    'title' => t('Impact-stack organization for logCRM'),
    'description' => t('This can be a sub-organization of the campaignion_organization variable.'),
    'type' => 'string',
    'default' => variable_get('campaignion_organization', 'impact-stack>' . basename(conf_path())),
    'localize' => FALSE,
  ];
  return $v;
}
