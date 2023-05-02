<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_newsletters\NewsletterList;
use Drupal\campaignion_newsletters\QueueItem;
use Drupal\campaignion_newsletters\Subscription;
use Drupal\little_helpers\Services\Container;
use Upal\DrupalUnitTestCase;

/**
 * Unit-test for the newsletter provider stub implementation.
 */
class NewsletterProviderTest extends DrupalUnitTestCase {

  /**
   * Create a provider with a stub client.
   */
  public function setUp() : void {
    parent::setUp();
    // Use the static constructor (required as interface) and inject the stub
    // API client using the services container.
    $this->client = $this->createMock(Client::class);
    $container = Container::get();
    $container->inject('campaignion_logcrm.client', $this->client);
    $this->provider = NewsletterProvider::fromParameters([]);
    $container->inject('campaignion_logcrm.client', NULL);
  }

  /**
   * Test getting the lists from logCRM.
   */
  public function testGetLists() {
    $result['lists']['foo'] = [
      'global_identifier' => 'provider:foo',
      'title' => 'Foo list',
    ];
    $this->client->method('get')->with(
      $this->equalTo('/newsletter/lists'),
      $this->equalTo(['organization' => variable_get_value('campaignion_organization')]),
    )->willReturn($result);
    $lists = $this->provider->getLists();
    $this->assertCount(1, $lists);
    $list = $lists[0];
    $this->assertInstanceOf(NewsletterList::class, $list);
    $this->assertEqual('provider:foo', $list->identifier);
    $this->assertEqual('Foo list', $list->title);
    $this->assertEqual('logcrm', $list->source);
    $this->assertNull($list->data);
  }

  /**
   * Test that getSubscribers method doesn’t do anything.
   */
  public function testGetSubscribers() {
    $this->client->expects($this->never())->method($this->anything());
    $list = $this->createMock(NewsletterList::class);
    $this->assertEqual([], $this->provider->getSubscribers($list));
  }

  /**
   * Test that the subscribe method doesn’t do anything.
   */
  public function testSubscribe() {
    $this->client->expects($this->never())->method($this->anything());
    $list = $this->createMock(NewsletterList::class);
    $item = $this->createMock(QueueItem::class);
    $this->assertNull($this->provider->subscribe($list, $item));
  }

  /**
   * Test that the update method doesn’t do anything.
   */
  public function testUpdate() {
    $this->client->expects($this->never())->method($this->anything());
    $list = $this->createMock(NewsletterList::class);
    $item = $this->createMock(QueueItem::class);
    $this->assertNull($this->provider->update($list, $item));
  }

  /**
   * Test that the unsubscribe method doesn’t do anything.
   */
  public function testUnsubscribe() {
    $this->client->expects($this->never())->method($this->anything());
    $list = $this->createMock(NewsletterList::class);
    $item = $this->createMock(QueueItem::class);
    $this->assertNull($this->provider->unsubscribe($list, $item));
  }

  /**
   * Test that the data method doesn’t do anything.
   */
  public function testData() {
    $this->client->expects($this->never())->method($this->anything());
    $subscription = $this->createMock(Subscription::class);
    $expected_data = [[], '5da0376863c6278e3c8804449bc1814ca4c4217b'];
    $this->assertEqual($expected_data, $this->provider->data($subscription, NULL));
  }

  /**
   * Test that the polling method doesn’t do anything.
   */
  public function testPolling() {
    $this->client->expects($this->never())->method($this->anything());
    $this->assertNull($this->provider->polling());
  }

}
