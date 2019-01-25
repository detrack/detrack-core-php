<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Model\Collection;
use Detrack\DetrackCore\Factory\ItemFactory;
use Detrack\DetrackCore\Model\Item;
use Detrack\DetrackCore\Model\Vehicle;
use Detrack\DetrackCore\Factory\CollectionFactory;
use Detrack\DetrackCore\Repository\Exception\MissingFieldException;
use Carbon\Carbon;

class CollectionTest extends TestCase
{
    protected $client;
    protected $testingCollection;
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
        $this->testingCollection = new Collection($attr);
        $this->testingCollection->setClient($this->client);
    }

    /**
     * Tests the constructor of the Collection object.
     *
     * @covers \Collection::__construct
     */
    public function testClassConstructor()
    {
        $attr = [
            'date' => Carbon::now()->toDateString(),
            'do' => ('D.O. '.rand(1, 999999999)),
            'address' => '61 Kaki Bukit Ave 1 #04-34, Shun Li Ind Park Singapore 417943',
            'items' => ItemFactory::fakes(10),
        ];
        $collection = new Collection($attr);
        $this->assertEquals($attr['date'], $collection->date);
        $this->assertEquals($attr['do'], $collection->do);
        $this->assertEquals($attr['address'], $collection->address);
        $this->assertEquals($attr['items'], $collection->items);
    }

    /**
     * Tests whether we can create a new collection via the save() method.
     *
     * Attributes for this testing collection are defined in the setUp method.
     *
     * @covers \Collection::save
     * @covers \DetrackClient::findCollection
     * @covers \Collection::getIdentifier
     * @covers \Collection::create
     */
    public function testCreateCollection()
    {
        $this->testingCollection->save();
        $this->assertNotNull($this->client->findCollection($this->testingCollection->getIdentifier()));
        $this->assertEquals($this->testingCollection->items, $this->client->findCollection($this->testingCollection->getIdentifier())->items);
        //tests if we can find collection with just the DO
        $this->assertEquals($this->testingCollection->items, $this->client->findCollection($this->testingCollection->do)->items);

        return $this->testingCollection;
    }

    /**
     * Tests whether collections can be updated also via the save() method.
     *
     * We will add the instructions "knock on the door, doorbell is not working" to the collection.
     * We will also add an additional item in the collection
     *
     * @depends testCreateCollection
     *
     * @covers \Collection::save()
     * @covers \Collection::update()
     * @covers \Collection::getIdentifier()
     * @covers \DetrackClient::findCollection();
     *
     * @param mixed $collection
     */
    public function testUpdateCollection($collection)
    {
        //test if we can update a collection with just the date
        $collection2 = new Collection();
        $newInstructions = 'knock on the door, doorbell is not working';
        $collection2->instructions = $newInstructions;
        $collection2->do = $collection->do;
        $collection2->address = $collection->address;
        $collection2->setClient($this->client);
        $collection2->save();
        $this->assertEquals($newInstructions, $this->client->findCollection($collection->getIdentifier())->instructions);
        $newItem = new Item();
        $newItem->sku = '9001';
        $newItem->desc = 'Refridgerator Haiku';
        $newItem->qty = 1; //the API will convert string to int
        $collection->items->add($newItem);
        $collection->save();
        $this->assertEquals($newItem, $this->client->findCollection($collection->getIdentifier())->items->last());
        //tests if I did not accidentally delete the instructions
        $this->assertEquals($newInstructions, $this->client->findCollection($collection->getIdentifier())->instructions);

        return $collection;
    }

    /**
     * Tests whether collections can be deleted via the delete() method.
     *
     * @depends testUpdateCollection
     *
     * @covers \Collection::delete();
     * @covers \Collection::getIdentifier();
     * @covers \DetrackClient::findCollection();
     *
     * @param mixed $collection
     */
    public function testDeleteCollection($collection)
    {
        $this->assertTrue($collection->delete());
        $this->assertNull($this->client->findCollection($collection->getIdentifier()));
    }

    /**
     * Tests if we can assign a vehicle to the collection, and view driver details.
     *
     * Requires the test driver/vehicle name to be set
     *
     * @covers \Collection::assignTo
     * @covers \Collection::setDriver
     * @covers \Collection::setVehicle
     * @covers \Collection::getVehicle
     * @covers \Collection::getDriver
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
        $newFactory = new CollectionFactory($this->client);
        //one for each alias of assignTo
        for ($i = 0; $i < 3; ++$i) {
            //one for each method (by string or by object)
            for ($j = 0; $j < 2; ++$j) {
                $sampleCollection = $newFactory->createFakes(1)[0];
                if ($i == 0) {
                    $sampleCollection->assignTo($j == 0 ? $retrievedVehicle : $retrievedVehicle->name);
                } elseif ($i == 1) {
                    $sampleCollection->setDriver($j == 0 ? $retrievedVehicle : $retrievedVehicle->name);
                } elseif ($i == 2) {
                    $sampleCollection->setVehicle($j == 0 ? $retrievedVehicle : $retrievedVehicle->name);
                }
                $this->assertEquals($retrievedVehicle->name, $sampleCollection->assign_to);
                $sampleCollection->save();
                $this->assertEquals($retrievedVehicle->name, $this->client->findCollection($sampleCollection->getIdentifier())->assign_to);
                $this->assertEquals($retrievedVehicle, $this->client->findCollection($sampleCollection->getIdentifier())->getDriver());
                $this->assertEquals($retrievedVehicle, $this->client->findCollection($sampleCollection->getIdentifier())->getVehicle());
                $sampleCollection->delete();
            }
        }
    }

    /**
     * Tests if we can retrieve an array of attributes in the Collection object.
     *
     * @covers \Collection::getAttributes
     */
    public function testGetAttributes()
    {
        $collection = new Collection();
        $collection->date = '2012-12-12';
        $collection->address = 'Null island';
        $collection->do = '666666';
        $this->assertInternalType('array', $collection->getAttributes());
        $this->assertNotEquals(0, count($collection->getAttributes()));
        $this->assertArrayHasKey('date', $collection->getAttributes());
        $this->assertArrayHasKey('do', $collection->getAttributes());
        $this->assertArrayHasKey('address', $collection->getAttributes());
        $this->assertNotNull($collection->getAttributes()['date']);
        $this->assertNotNull($collection->getAttributes()['address']);
        $this->assertNotNull($collection->getAttributes()['do']);
    }

    /**
     * Tests if entering bad attribitues will result in an exception being thrown in saves.
     *
     * @covers \CollectionRepository::create
     */
    public function testBadSave()
    {
        $badCollection = new Collection();
        $badCollection->setClient($this->client);
        $badCollection->date = '2012-12-12';
        $badCollection->address = 'Exception Island';
        //don't set a do
        $this->expectException(MissingFieldException::class);
        $badCollection->save();
    }

    /**
     * Another test if entering bad attributes will result in an exception being thrown in saves.
     *
     * @covers \CollectionRepository::create
     */
    public function testBadSave2()
    {
        $badCollection = new Collection();
        $badCollection->setClient($this->client);
        $badCollection->date = '2012-12-12';
        $badCollection->do = 'DEADBEEF';
        //don't set address
        $this->expectException(MissingFieldException::class);
        $badCollection->save();
    }

    /**
     ** Tests for concurrent save issues – two objects referring to the same Detrack Collection haing different attributes.
     */
    public function testConcurrentSave()
    {
        $testCollection = new Collection();
        $testCollection->setClient($this->client);
        $testCollection->date = date('Y-m-d');
        $testCollection->do = 'ConcurrencyTest';
        $testCollection->address = 'PHP Island';
        $testCollection->save();

        $testCollection1 = $this->client->bulkFindCollections([$testCollection])[0];
        $testCollection2 = $this->client->bulkFindCollections([$testCollection])[0];
        $testCollection1->instructions = 'This should not get overwritten';
        $testCollection2->collect_from = 'This should not get overwritten either';
        $testCollection1->setClient($this->client);
        $testCollection2->setClient($this->client);
        $testCollection1->save();
        $testCollection2->save();

        $resultCollection = $this->client->bulkFindCollections([$testCollection])[0];
        $this->assertEquals($resultCollection->instructions, 'This should not get overwritten');
        $this->assertEquals($resultCollection->collect_from, 'This should not get overwritten either');
    }
}
