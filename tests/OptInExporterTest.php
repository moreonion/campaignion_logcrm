<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_opt_in\Values;
use Drupal\little_helpers\Webform\Submission;
use Upal\DrupalUnitTestCase;

/**
 * Test exporting opt-ins.
 */
class OptInExporterTest extends DrupalUnitTestCase {

  /**
   * Test exporting opt-ins.
   */
  public function testExport() {
    $submission = $this->createMock(Submission::class);
    $s['opt_in'] = $this->createMock(Values::class);
    $submission->method('__isset')->will($this->returnCallback(function ($prop) use ($s) {
      return isset($s[$prop]);
    }));
    $submission->method('__get')->will($this->returnCallback(function ($prop) use ($s) {
      return $s[$prop];
    }));
    $submission->opt_in->expects($this->any())->method('values')->willReturn([
      1 => [
        'value' => 'opt-in',
        'raw_value' => 'radios:opt-in',
        'channel' => 'email',
      ],
      2 => [
        'value' => 'opt-out',
        'raw_value' => 'checkbox:opt-out',
        'channel' => 'post',
      ],
    ]);
    $node = (object) [];
    $node->webform['components'][1]['extra'] = [
      'lists' => ['1' => '1', '2' => '2'],
    ];
    $submission->node = $node;
    $submission->method('valueByKey')->with($this->equalTo('email'))->willReturn('test@example.com');
    $exporter = new OptInExporter([1 => 'known-list'], '1.1.1.1');
    $this->assertEqual([
      1 => [
        'operation' => 'opt-in',
        'value' => 'radios:opt-in',
        'channel' => 'email',
        'address' => 'test@example.com',
        'unsubscribe_all' => FALSE,
        'unsubscribe_unknown' => FALSE,
        'trigger_opt_in_email' => TRUE,
        'trigger_welcome_email' => FALSE,
        'lists' => ['known-list'],
        'ip_address' => '1.1.1.1',
      ],
      2 => [
        'operation' => 'opt-out',
        'value' => 'checkbox:opt-out',
        'channel' => 'post',
      ],
    ], $exporter->export($submission));
  }

}
