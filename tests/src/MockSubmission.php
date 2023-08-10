<?php

namespace Drupal\campaignion_logcrm\Tests;

use Drupal\little_helpers\Webform\Submission;

/**
 * Webform submission object that adds more defaults for easier mocking.
 */
class MockSubmission extends Submission {

  /**
   * Add defaults to a node’s webform components.
   *
   * Long term this could go into \Drupal\little_helpers\Webform\Webform.
   */
  public static function prepareComponents($node) {
    foreach ($node->webform['components'] as $cid => &$component) {
      webform_component_defaults($component);
      $component += [
        'pid' => 0,
        'cid' => $cid,
        'name' => $component['form_key'],
        'weight' => 0,
      ];
    }
  }

  /**
   * Create a submission by just passing the an object and optional components.
   */
  public static function createWithComponents($submission, array $components = [], $node = NULL) {
    if (!$node) {
      $node = (object) ["webform" => ["components" => []]];
    }
    static::prepareComponents($node);
    $node->webform["components"] = $components += $node->webform["components"];
    return new static($node, $submission);
  }

}
