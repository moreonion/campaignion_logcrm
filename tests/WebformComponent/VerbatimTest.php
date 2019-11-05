<?php

namespace Drupal\campaignion_logcrm\WebformComponent;

use Drupal\little_helpers\Webform\Submission;
use Upal\DrupalUnitTestCase;

/**
 * Test the default webform value exporter.
 */
class VerbatimTest extends DrupalUnitTestCase {

  /**
   * Test getting a value from a submission.
   */
  public function testValue() {
    $submission = $this->createMock(Submission::class);
    $submission->method('valuesByCid')->will($this->returnValue(['first', 'second']));
    $component = ['cid' => 1];
    $exporter = new Verbatim(NULL);
    $this->assertEqual('first', $exporter->value($component, $submission));
  }

}
