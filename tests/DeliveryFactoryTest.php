<?php

require_once "CreateClientTrait.php";

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Factory\DeliveryFactory;
use Detrack\DetrackCore\Client\Exception\InvalidAPIKeyException;
use Detrack\DetrackCore\Model\Delivey;

class DeliveryFactoryTest extends TestCase
{
  protected $client;
  use CreateClientTrait;
  public function setUp(){
    $this->createClient();
  }
  /**
  * Tests if we can create fakes from the Factory
  *
  * @covers DeliveryFactory::createFakes
  */
  public function testCreateFakes(){
    $newFactory = new DeliveryFactory($this->client);
    $randomNumber = rand(0,99);
    $newDeliveries = $newFactory->createFakes($randomNumber);
    $this->assertTrue(is_array($newDeliveries));
    $this->assertEquals("Detrack\DetrackCore\Model\Delivery",get_class($newDeliveries[rand(0,$randomNumber)]));
  }
}
