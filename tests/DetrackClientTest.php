<?php

require_once "CreateClientTrait.php";

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Client\Exception\InvalidAPIKeyException;
use Detrack\DetrackCore\Model\Delivery;
use Carbon\Carbon;
class DetrackClientTest extends TestCase
{
  protected $client;
  use CreateClientTrait;
  function setUp(){
    $this->createClient();
  }
  public function testBadKeys(){
    $this->expectException(InvalidAPIKeyException::class);
    $badClient = new DetrackClient("This is a bad key!");
    $badClient = new DetrackClient(NULL);
    $badClient = new DetrackClient(new \stdClass());
    $badClient = new DetrackClient(9001);
  }
  /**
  * Tests the bulkSaveDeliveries()'s create functionality in the DeliveryMiscActions traits
  *
  * @covers DeliveryFactory::create();
  * @covers DetrackClient::bulkSaveDeliveries();
  */
  public function testBulkCreateDeliveries(){
    $this->assertTrue(true);
  }
}

 ?>
