<?php

require_once "CreateClientTrait.php";

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Client\Exception\InvalidAPIKeyException;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Factory\DeliveryFactory;

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
    $newFactory = new DeliveryFactory($this->client);
    $newDeliveries = $newFactory->createFakes(rand(101,301));
    //ensure nothing broke during the bulkSaveDeliveries call
    $this->assertTrue($this->client->bulkSaveDeliveries($newDeliveries));
    //now we try mixing create and update and see how it goes
    $newDeliveries2 = $newFactory->createFakes(rand(101,301));
    echo "\nnewDeliveries: " . count($newDeliveries);
    echo "\nnewDeliveries: " . count($newDeliveries2);
    foreach($newDeliveries as $newDelivery){
      //modify some fields
      $newDelivery->instructions = "lorem ipsum bottom kek";
    }
    $combinedDeliveries = array_merge_recursive($newDeliveries,$newDeliveries2);
    shuffle($combinedDeliveries);
    //ensure nothing broke during the bulkSaveDeliveries call
    $this->assertTrue($this->client->bulkSaveDeliveries($combinedDeliveries));
    //sample one random delivery from $newDeliveries and $newDeliveries2
    $sampleDelivery = $newDeliveries[rand(0,count($newDeliveries)-1)];
    $sampleDelivery2 = $newDeliveries2[rand(0,count($newDeliveries2)-1)];
    //test if update worked
    $this->assertEquals("lorem ipsum bottom kek",$this->client->findDelivery($sampleDelivery->getIdentifier())->instructions);
    //test if create worked
    $this->assertNotNull($this->client->findDelivery($sampleDelivery2->getIdentifier()));
  }
}

 ?>
