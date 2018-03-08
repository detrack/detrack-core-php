<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Factory\ItemFactory;
use Detrack\DetrackCore\Model\Item;
use Detrack\DetrackCore\Model\Vehicle;
use Detrack\DetrackCore\Factory\DeliveryFactory;
use Detrack\DetrackCore\Repository\Exception\MissingFieldException;
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
    $this->assertTrue($delivery->delete());
    $this->assertNull($this->client->findDelivery($delivery->getIdentifier()));
  }
  /**
  * Tests if we can assign a vehicle to the delivery, and view driver details
  *
  * Requires the test driver/vehicle name to be set
  *
  * @covers Delivery::assignTo
  * @covers Delivery::setDriver
  * @covers Delivery::setVehicle
  * @covers Delivery::getVehicle
  * @covers Delivery::getDriver
  */
  public function testAssignTo(){
    if(getenv("TEST_VEHICLE_NAME")==NULL){
      $this->markTestSkipped("This test requires the TEST_VEHICLE_NAME in .env to be set.");
    }else{
      $retrievedVehicle = $this->client->findVehicle(getenv("TEST_VEHICLE_NAME"));
      $this->assertNotNull($retrievedVehicle);
      $this->assertInstanceOf(Vehicle::class, $retrievedVehicle);
      $this->assertEquals(getenv("TEST_VEHICLE_NAME"),$retrievedVehicle->name);
    }
    $newFactory = new DeliveryFactory($this->client);
    //one for each alias of assignTo
    for($i=0;$i<3;$i++){
      //one for each method (by string or by object)
      for($j=0;$j<2;$j++){
        $sampleDelivery = $newFactory->createFakes(1)[0];
        if($i==0){
          $sampleDelivery->assignTo($j==0 ? $retrievedVehicle : $retrievedVehicle->name);
        }else if($i==1){
          $sampleDelivery->setDriver($j==0 ? $retrievedVehicle : $retrievedVehicle->name);
        }else if($i==2){
          $sampleDelivery->setVehicle($j==0 ? $retrievedVehicle : $retrievedVehicle->name);
        }
        $this->assertEquals($retrievedVehicle->name,$sampleDelivery->assign_to);
        $sampleDelivery->save();
        $this->assertEquals($retrievedVehicle->name,$this->client->findDelivery($sampleDelivery->getIdentifier())->assign_to);
        $this->assertEquals($retrievedVehicle,$this->client->findDelivery($sampleDelivery->getIdentifier())->getDriver());
        $this->assertEquals($retrievedVehicle,$this->client->findDelivery($sampleDelivery->getIdentifier())->getVehicle());
        $sampleDelivery->delete();
      }
    }
  }
  /**
  * Tests if entering bad attribitues will result in an exception being thrown in saves
  *
  * @covers DeliveryRepository::create
  */
  public function testBadSave(){
    $badDelivery = new Delivery();
    $badDelivery->setClient($this->client);
    $badDelivery->do = "DEADBEEF";
    $badDelivery->address = "Exception Island";
    //don't set a date
    $this->expectException(MissingFieldException::class);
    $badDelivery->save();
  }
  /**
  * Another test if entering bad attributes will result in an exception being thrown in saves
  *
  * @covers DeliveryRepository::create
  */
  public function testBadSave2(){
    $badDelivery = new Delivery();
    $badDelivery->setClient($this->client);
    $badDelivery->date = "2012-12-12";
    $badDelivery->address = "Exception Island";
    //don't set a do
    $this->expectException(MissingFieldException::class);
    $badDelivery->save();
  }
  /**
  * Yet another bad test
  */
  public function testBadSave3(){
    $badDelivery = new Delivery();
    $badDelivery->setClient($this->client);
    $badDelivery->date = "2012-12-12";
    $badDelivery->do = "DEADBEEF";
    //don't set address
    $this->expectException(MissingFieldException::class);
    $badDelivery->save();
  }
}

?>
