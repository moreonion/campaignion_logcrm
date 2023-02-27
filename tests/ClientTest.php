<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_auth\AuthAppClient;
use Upal\DrupalUnitTestCase;

/**
 * Test the API-client class.
 */
class ClientTest extends DrupalUnitTestCase {

  /**
   * Create an instrumented Api object that doesn’t actually send requests.
   */
  protected function instrumentedApi() {
    $auth = $this->createMock(AuthAppClient::class);
    $api = $this->getMockBuilder(Client::class)
      ->setConstructorArgs(['http://mock', $auth, 'impact-stack>example'])
      ->setMethods(['send'])
      ->getMock();
    return $api;
  }

  /**
   * Test sending a simple event.
   */
  public function testSendEvent() {
    $api = $this->instrumentedApi();
    $input_event_data = ['email' => 'test@example.com'];
    $sent_event_data = [
      'organization' => 'impact-stack>example',
    ] + $input_event_data;
    $event = $this->createMock(Event::class);
    $event->method('toArray')
      ->will($this->returnValue($input_event_data));
    $api->expects($this->once())
      ->method('send')
      ->with(
        $this->equalTo('/events'),
        $this->equalTo([]),
        $this->equalTo($sent_event_data)
      );
    $api->sendEvent($event);
  }

}
