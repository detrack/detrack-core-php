<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Factory\ItemFactory;
use Detrack\DetrackCore\Model\Item;
use Carbon\Carbon;

class DeliveryTest extends TestCase{
  protected $client;
  protected $testingDelivery;
  use CreateClientTrait;
  protected function setUp(){
    $this->createClient(); //from createClientTrait;
    $attr = [
      "date"=>Carbon::now()->toDateString(),
      "do"=>("D.O. " . rand(1,999999999)),
      "address"=>"61 Kaki Bukit Ave 1 #04-34, Shun Li Ind Park Singapore 417943",
      "items" => ItemFactory::fakes(10),
    ];
    $this->testingDelivery = new Delivery($attr);
    $this->testingDelivery->setClient($this->client);
  }
  /**
  * Tests the constructor of the Delivery object
  *
  * @covers Delivery::__construct
  */
  function testClassConstructor(){
    $attr = [
      "date"=>Carbon::now()->toDateString(),
      "do"=>("D.O. " . rand(1,999999999)),
      "address"=>"61 Kaki Bukit Ave 1 #04-34, Shun Li Ind Park Singapore 417943",
      "items" => ItemFactory::fakes(10),
    ];
    $delivery = new Delivery($attr);
    $this->assertEquals($attr["date"], $delivery->date);
    $this->assertEquals($attr["do"], $delivery->do);
    $this->assertEquals($attr["address"], $delivery->address);
    $this->assertEquals($attr["items"], $delivery->items);
  }
  /**
  * Tests whether we can create a new delivery via the save() method.
  *
  * Attributes for this testing delivery are defined in the setUp method.
  *
  * @covers Delivery::save
  * @covers DetrackClient::findDelivery
  * @covers Delivery::getIdentifier
  * @covers Delivery::create
  */
  function testCreateDelivery(){
    echo "\n Testing create function \n";
    $this->testingDelivery->save();
    $this->assertNotNull($this->client->findDelivery($this->testingDelivery->getIdentifier()));
    $this->assertEquals($this->testingDelivery->items,$this->client->findDelivery($this->testingDelivery->getIdentifier())->items);
    return $this->testingDelivery;
  }
  /**
  * Tests whether deliveries can be updated also via the save() method.
  *
  * We will add the instructions "knock on the door, doorbell is not working" to the delivery.
  * We will also add an additional item in the delivery
  *
  * @depends testCreateDelivery
  *
  * @covers Delivery::save()
  * @covers Delivery::update()
  * @covers Delivery::getIdentifier()
  * @covers DetrackClient::findDelivery();
  */
  function testUpdateDelivery($delivery){
    echo "\n Testing update function \n";
    $newInstructions = "knock on the door, doorbell is not working";
    $delivery->instructions = $newInstructions;
    $newItem = new Item();
    $newItem->sku = "9001";
    $newItem->desc = "Refridgerator Haiku";
    $newItem->qty = 1; //the API will convert string to int
    $delivery->items->add($newItem);
    $delivery->save();
    $this->assertEquals($newInstructions,$this->client->findDelivery($delivery->getIdentifier())->instructions);
    $this->assertEquals($newItem,$this->client->findDelivery($delivery->getIdentifier())->items->last());
    return $delivery;
  }
  /**
  * Tests whether deliveries can be deleted via the delete() method.
  *
  * @depends testUpdateDelivery
  *
  * @covers Delivery::delete();
  * @covers Delivery::getIdentifier();
  * @covers DetrackClient::findDelivery();
  */
  function testDeleteDelivery($delivery){
    echo "\n Testing delete function \n";
    $this->assertTrue($delivery->delete());
    $this->assertNull($this->client->findDelivery($delivery->getIdentifier()));
  }
}

?>
