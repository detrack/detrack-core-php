<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Factory\ItemFactory;
use Detrack\DetrackCore\Model\Item;
use Detrack\DetrackCore\Model\Vehicle;
use Detrack\DetrackCore\Factory\DeliveryFactory;
use Detrack\DetrackCore\Repository\Exception\MissingFieldException;
use Carbon\Carbon;

class DeliveryTest extends TestCase
{
    protected $client;
    protected $testingDelivery;
    use CreateClientTrait;

    protected function setUp()
    {
        $this->createClient(); //from createClientTrait;
        $attr = [
            'date' => Carbon::now()->toDateString(),
            'do' => ('D.O. '.rand(1, 999999999)),
            'address' => '61 Kaki Bukit Ave 1 #04-34, Shun Li Ind Park Singapore 417943',
            'items' => ItemFactory::fakes(10),
        ];
        $this->testingDelivery = new Delivery($attr);
        $this->testingDelivery->setClient($this->client);
    }

    /**
     * Tests the constructor of the Delivery object.
     *
     * @covers \Delivery::__construct
     */
    public function testClassConstructor()
    {
        $attr = [
            'date' => Carbon::now()->toDateString(),
            'do' => ('D.O. '.rand(1, 999999999)),
            'address' => '61 Kaki Bukit Ave 1 #04-34, Shun Li Ind Park Singapore 417943',
            'items' => ItemFactory::fakes(10),
        ];
        $delivery = new Delivery($attr);
        $this->assertEquals($attr['date'], $delivery->date);
        $this->assertEquals($attr['do'], $delivery->do);
        $this->assertEquals($attr['address'], $delivery->address);
        $this->assertEquals($attr['items'], $delivery->items);
    }

    /**
     * Tests whether we can create a new delivery via the save() method.
     *
     * Attributes for this testing delivery are defined in the setUp method.
     *
     * @covers \Delivery::save
     * @covers \DetrackClient::findDelivery
     * @covers \Delivery::getIdentifier
     * @covers \Delivery::create
     */
    public function testCreateDelivery()
    {
        $this->testingDelivery->save();
        $this->assertNotNull($this->client->findDelivery($this->testingDelivery->getIdentifier()));
        $this->assertEquals($this->testingDelivery->items, $this->client->findDelivery($this->testingDelivery->getIdentifier())->items);
        //tests if we can find delivery with just the DO
        $this->assertEquals($this->testingDelivery->items, $this->client->findDelivery($this->testingDelivery->do)->items);

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
     * @covers \Delivery::save()
     * @covers \Delivery::update()
     * @covers \Delivery::getIdentifier()
     * @covers \DetrackClient::findDelivery();
     */
    public function testUpdateDelivery($delivery)
    {
        //test if we can update a delivery with just the date
        $delivery2 = new Delivery();
        $newInstructions = 'knock on the door, doorbell is not working';
        $delivery2->instructions = $newInstructions;
        $delivery2->do = $delivery->do;
        $delivery2->address = $delivery->address;
        $delivery2->setClient($this->client);
        $delivery2->save();
        $this->assertEquals($newInstructions, $this->client->findDelivery($delivery->getIdentifier())->instructions);
        $newItem = new Item();
        $newItem->sku = '9001';
        $newItem->desc = 'Refridgerator Haiku';
        $newItem->qty = 1; //the API will convert string to int
        $delivery->items->add($newItem);
        $delivery->save();
        $this->assertEquals($newItem, $this->client->findDelivery($delivery->getIdentifier())->items->last());
        //tests if I did not accidentally delete the instructions
        $this->assertEquals($newInstructions, $this->client->findDelivery($delivery->getIdentifier())->instructions);

        return $delivery;
    }

    /**
     * Tests whether deliveries can be deleted via the delete() method.
     *
     * @depends testUpdateDelivery
     *
     * @covers \Delivery::delete();
     * @covers \Delivery::getIdentifier();
     * @covers \DetrackClient::findDelivery();
     */
    public function testDeleteDelivery($delivery)
    {
        $this->assertTrue($delivery->delete());
        $this->assertNull($this->client->findDelivery($delivery->getIdentifier()));
    }

    /**
     * Tests if we can assign a vehicle to the delivery, and view driver details.
     *
     * Requires the test driver/vehicle name to be set
     *
     * @covers \Delivery::assignTo
     * @covers \Delivery::setDriver
     * @covers \Delivery::setVehicle
     * @covers \Delivery::getVehicle
     * @covers \Delivery::getDriver
     */
    public function testAssignTo()
    {
        if (getenv('TEST_VEHICLE_NAME') == null) {
            $this->markTestSkipped('This test requires the TEST_VEHICLE_NAME in .env to be set.');
        } else {
            $retrievedVehicle = $this->client->findVehicle(getenv('TEST_VEHICLE_NAME'));
            $this->assertNotNull($retrievedVehicle);
            $this->assertInstanceOf(Vehicle::class, $retrievedVehicle);
            $this->assertEquals(getenv('TEST_VEHICLE_NAME'), $retrievedVehicle->name);
        }
        $newFactory = new DeliveryFactory($this->client);
        //one for each alias of assignTo
        for ($i = 0; $i < 3; ++$i) {
            //one for each method (by string or by object)
            for ($j = 0; $j < 2; ++$j) {
                $sampleDelivery = $newFactory->createFakes(1)[0];
                if ($i == 0) {
                    $sampleDelivery->assignTo($j == 0 ? $retrievedVehicle : $retrievedVehicle->name);
                } elseif ($i == 1) {
                    $sampleDelivery->setDriver($j == 0 ? $retrievedVehicle : $retrievedVehicle->name);
                } elseif ($i == 2) {
                    $sampleDelivery->setVehicle($j == 0 ? $retrievedVehicle : $retrievedVehicle->name);
                }
                $this->assertEquals($retrievedVehicle->name, $sampleDelivery->assign_to);
                $sampleDelivery->save();
                $this->assertEquals($retrievedVehicle->name, $this->client->findDelivery($sampleDelivery->getIdentifier())->assign_to);
                $this->assertEquals($retrievedVehicle, $this->client->findDelivery($sampleDelivery->getIdentifier())->getDriver());
                $this->assertEquals($retrievedVehicle, $this->client->findDelivery($sampleDelivery->getIdentifier())->getVehicle());
                $sampleDelivery->delete();
            }
        }
    }

    /**
     * Tests if we can retrieve an array of attributes in the Delivery object.
     *
     * @covers \Delivery::getAttributes
     */
    public function testGetAttributes()
    {
        $delivery = new Delivery();
        $delivery->date = '2012-12-12';
        $delivery->address = 'Null island';
        $delivery->do = '666666';
        $this->assertInternalType('array', $delivery->getAttributes());
        $this->assertNotEquals(0, count($delivery->getAttributes()));
        $this->assertArrayHasKey('date', $delivery->getAttributes());
        $this->assertArrayHasKey('do', $delivery->getAttributes());
        $this->assertArrayHasKey('address', $delivery->getAttributes());
        $this->assertNotNull($delivery->getAttributes()['date']);
        $this->assertNotNull($delivery->getAttributes()['address']);
        $this->assertNotNull($delivery->getAttributes()['do']);
    }

    /**
     * Tests if entering bad attribitues will result in an exception being thrown in saves.
     *
     * @covers \DeliveryRepository::create
     */
    public function testBadSave()
    {
        $badDelivery = new Delivery();
        $badDelivery->setClient($this->client);
        $badDelivery->date = '2012-12-12';
        $badDelivery->address = 'Exception Island';
        //don't set a do
        $this->expectException(MissingFieldException::class);
        $badDelivery->save();
    }

    /**
     * Another test if entering bad attributes will result in an exception being thrown in saves.
     *
     * @covers \DeliveryRepository::create
     */
    public function testBadSave2()
    {
        $badDelivery = new Delivery();
        $badDelivery->setClient($this->client);
        $badDelivery->date = '2012-12-12';
        $badDelivery->do = 'DEADBEEF';
        //don't set address
        $this->expectException(MissingFieldException::class);
        $badDelivery->save();
    }

    /**
     ** Tests for concurrent save issues – two objects referring to the same Detrack Delivery haing different attributes.
     */
    public function testConcurrentSave()
    {
        $testDelivery = new Delivery();
        $testDelivery->setClient($this->client);
        $testDelivery->date = date('Y-m-d');
        $testDelivery->do = 'ConcurrencyTest';
        $testDelivery->address = 'PHP Island';
        $testDelivery->save();

        $testDelivery1 = $this->client->bulkFindDeliveries([$testDelivery])[0];
        $testDelivery2 = $this->client->bulkFindDeliveries([$testDelivery])[0];
        $testDelivery1->instructions = 'This should not get overwritten';
        $testDelivery2->deliver_to = 'This should not get overwritten either';
        $testDelivery1->setClient($this->client);
        $testDelivery2->setClient($this->client);
        $testDelivery1->save();
        $testDelivery2->save();

        $resultDelivery = $this->client->bulkFindDeliveries([$testDelivery])[0];
        $this->assertEquals($resultDelivery->instructions, 'This should not get overwritten');
        $this->assertEquals($resultDelivery->deliver_to, 'This should not get overwritten either');
    }
}
