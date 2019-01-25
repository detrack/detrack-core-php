<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Client\Exception\InvalidAPIKeyException;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Model\Collection;
use Detrack\DetrackCore\Factory\DeliveryFactory;
use Detrack\DetrackCore\Factory\CollectionFactory;
use Detrack\DetrackCore\Model\Vehicle;

class DetrackClientTest extends TestCase
{
    protected $client;
    use CreateClientTrait;

    public function setUp()
    {
        $this->createClient();
    }

    public function testBadKeys()
    {
        $this->expectException(InvalidAPIKeyException::class);
        $badClient = new DetrackClient('This is a bad key!');
        $badClient = new DetrackClient(null);
        $badClient = new DetrackClient(new \stdClass());
        $badClient = new DetrackClient(9001);
    }

    /**
     * Tests the bulkSaveDeliveries()'s create functionality in the DeliveryMiscActions traits.
     *
     * @covers \DeliveryFactory::create();
     * @covers \DeliveryMiscActions::bulkSaveDeliveries();
     */
    public function testBulkSaveDeliveries()
    {
        $newFactory = new DeliveryFactory($this->client);
        $newDeliveries = $newFactory->createFakes(rand(101, 150));
        //ensure nothing broke during the bulkSaveDeliveries call
        $this->assertTrue($this->client->bulkSaveDeliveries($newDeliveries));
        //now we try mixing create and update and see how it goes
        $newDeliveries2 = $newFactory->createFakes(rand(101, 150));
        foreach ($newDeliveries as $newDelivery) {
            //modify some fields
            $newDelivery->instructions = 'lorem ipsum bottom kek';
        }
        $combinedDeliveries = array_merge_recursive($newDeliveries, $newDeliveries2);
        shuffle($combinedDeliveries);
        //ensure nothing broke during the bulkSaveDeliveries call
        $this->assertTrue($this->client->bulkSaveDeliveries($combinedDeliveries));
        //sample one random delivery from $newDeliveries and $newDeliveries2
        $sampleDelivery = $newDeliveries[rand(0, count($newDeliveries) - 1)];
        $sampleDelivery2 = $newDeliveries2[rand(0, count($newDeliveries2) - 1)];
        //test if update worked
        $this->assertEquals('lorem ipsum bottom kek', $this->client->findDelivery($sampleDelivery->getIdentifier())->instructions);
        //test if create worked
        $this->assertNotNull($this->client->findDelivery($sampleDelivery2->getIdentifier()));

        return $combinedDeliveries;
    }

    /**
     * Tests the bulkFindDeliveries function to see if we can find the deliveries we just created.
     *
     * @depends testBulkSaveDeliveries
     *
     * @covers \DeliveryMiscActions::bulkFindDeliveries;
     *
     * @param mixed $combinedDeliveries
     */
    public function testBulkFindDeliveries($combinedDeliveries)
    {
        //first test finding via delivery objects
        $this->assertEquals(count($combinedDeliveries), count($this->client->bulkFindDeliveries($combinedDeliveries)));
        //then test via delivery identifiers
        $combinedDeliveryIdentifiers = [];
        foreach ($combinedDeliveries as $combinedDelivery) {
            array_push($combinedDeliveryIdentifiers, $combinedDelivery->getIdentifier());
        }
        $this->assertEquals(count($combinedDeliveries), count($this->client->bulkFindDeliveries($combinedDeliveryIdentifiers)));

        return $combinedDeliveries;
    }

    /**
     * Test the findDeliveriesByDate function to see if we can list all deliveries created today.
     *
     * @depends testBulkFindDeliveries
     *
     * @covers \DeliveryMiscActions::findDeliveriesByDate
     *
     * @param mixed $combinedDeliveries
     */
    public function testFindDeliveriesByDate($combinedDeliveries)
    {
        $receivedDeliveries = $this->client->findDeliveriesByDate($combinedDeliveries[0]->date);
        $this->assertInstanceOf(Delivery::class, $receivedDeliveries[rand(0, count($receivedDeliveries))]);
        $this->assertGreaterThanOrEqual(count($combinedDeliveries), count($receivedDeliveries));

        return $combinedDeliveries;
    }

    /**
     * Tests the bulkDeleteDeliveries function to see if we can delete some of the deliveries we created today.
     *
     * @depends testFindDeliveriesByDate
     *
     * @covers \DeliveryMiscActions::bulkDeleteDeliveries
     *
     * @param mixed $combinedDeliveries
     */
    public function testBulkDeleteDeliveries($combinedDeliveries)
    {
        //choose a subset of deliveries to deletes
        $toBeDeleted = array_slice($combinedDeliveries, 0, rand(1, count($combinedDeliveries) - 1));
        $this->assertSame(true, $this->client->bulkDeleteDeliveries($toBeDeleted));
        $this->assertSame([], $this->client->bulkFindDeliveries($toBeDeleted));

        return array_slice($combinedDeliveries, count($toBeDeleted));
    }

    /**
     * Tests the deleteDeliveriesByDate function to see if we can delete all deliveries created today.
     *
     * @depends testBulkDeleteDeliveries
     *
     * @covers \DeliveryMiscActions::deleteDeliveriesByDate
     *
     * @param mixed $combinedDeliveries
     */
    public function testDeleteDeliveriesByDate($combinedDeliveries)
    {
        $this->assertSame(true, $this->client->deleteDeliveriesByDate($combinedDeliveries[0]->date));
        $this->assertSame([], $this->client->findDeliveriesByDate($combinedDeliveries[0]->date));
    }

    /**
     * Tests the bulkSaveCollections()'s create functionality in the CollectionMiscActions traits.
     *
     * @covers \CollectionFactory::create();
     * @covers \CollectionMiscActions::bulkSaveCollections();
     */
    public function testBulkSaveCollections()
    {
        $newFactory = new CollectionFactory($this->client);
        $newCollections = $newFactory->createFakes(rand(101, 150));
        //ensure nothing broke during the bulkSaveCollections call
        $this->assertTrue($this->client->bulkSaveCollections($newCollections));
        //now we try mixing create and update and see how it goes
        $newCollections2 = $newFactory->createFakes(rand(101, 150));
        foreach ($newCollections as $newCollection) {
            //modify some fields
            $newCollection->instructions = 'lorem ipsum bottom kek';
        }
        $combinedCollections = array_merge_recursive($newCollections, $newCollections2);
        shuffle($combinedCollections);
        //ensure nothing broke during the bulkSaveCollections call
        $this->assertTrue($this->client->bulkSaveCollections($combinedCollections));
        //sample one random collection from $newCollections and $newCollections2
        $sampleCollection = $newCollections[rand(0, count($newCollections) - 1)];
        $sampleCollection2 = $newCollections2[rand(0, count($newCollections2) - 1)];
        //test if update worked
        $this->assertEquals('lorem ipsum bottom kek', $this->client->findCollection($sampleCollection->getIdentifier())->instructions);
        //test if create worked
        $this->assertNotNull($this->client->findCollection($sampleCollection2->getIdentifier()));

        return $combinedCollections;
    }

    /**
     * Tests the bulkFindCollections function to see if we can find the collections we just created.
     *
     * @depends testBulkSaveCollections
     *
     * @covers \CollectionMiscActions::bulkFindCollections;
     *
     * @param mixed $combinedCollections
     */
    public function testBulkFindCollections($combinedCollections)
    {
        //first test finding via collection objects
        $this->assertEquals(count($combinedCollections), count($this->client->bulkFindCollections($combinedCollections)));
        //then test via collection identifiers
        $combinedCollectionIdentifiers = [];
        foreach ($combinedCollections as $combinedCollection) {
            array_push($combinedCollectionIdentifiers, $combinedCollection->getIdentifier());
        }
        $this->assertEquals(count($combinedCollections), count($this->client->bulkFindCollections($combinedCollectionIdentifiers)));

        return $combinedCollections;
    }

    /**
     * Test the findCollectionsByDate function to see if we can list all collections created today.
     *
     * @depends testBulkFindCollections
     *
     * @covers \CollectionMiscActions::findCollectionsByDate
     *
     * @param mixed $combinedCollections
     */
    public function testFindCollectionsByDate($combinedCollections)
    {
        $receivedCollections = $this->client->findCollectionsByDate($combinedCollections[0]->date);
        $this->assertInstanceOf(Collection::class, $receivedCollections[rand(0, count($receivedCollections))]);
        $this->assertGreaterThanOrEqual(count($combinedCollections), count($receivedCollections));

        return $combinedCollections;
    }

    /**
     * Tests the bulkDeleteCollections function to see if we can delete some of the collections we created today.
     *
     * @depends testFindCollectionsByDate
     *
     * @covers \CollectionMiscActions::bulkDeleteCollections
     *
     * @param mixed $combinedCollections
     */
    public function testBulkDeleteCollections($combinedCollections)
    {
        //choose a subset of collections to deletes
        $toBeDeleted = array_slice($combinedCollections, 0, rand(1, count($combinedCollections) - 1));
        $this->assertSame(true, $this->client->bulkDeleteCollections($toBeDeleted));
        $this->assertSame([], $this->client->bulkFindCollections($toBeDeleted));

        return array_slice($combinedCollections, count($toBeDeleted));
    }

    /**
     * Tests the deleteCollectionsByDate function to see if we can delete all collections created today.
     *
     * @depends testBulkDeleteCollections
     *
     * @covers \CollectionMiscActions::deleteCollectionsByDate
     *
     * @param mixed $combinedCollections
     */
    public function testDeleteCollectionsByDate($combinedCollections)
    {
        $this->assertSame(true, $this->client->deleteCollectionsByDate($combinedCollections[0]->date));
        $this->assertSame([], $this->client->findCollectionsByDate($combinedCollections[0]->date));
    }

    /**
     * Tests the findVehicle function to see if we can retrieve details of a certain vehicle.
     *
     * Requires the test driver/vehicle name to be set
     *
     * @covers \DetrackClient::findVehicle
     */
    public function testFindVehicle()
    {
        if (getenv('TEST_VEHICLE_NAME') == null) {
            $this->markTestSkipped('This test requires the TEST_VEHICLE_NAME in .env to be set.');
        } else {
            $retrievedVehicle = $this->client->findVehicle(getenv('TEST_VEHICLE_NAME'));
            $this->assertNotNull($retrievedVehicle);
            $this->assertInstanceOf(Vehicle::class, $retrievedVehicle);
            $this->assertEquals(getenv('TEST_VEHICLE_NAME'), $retrievedVehicle->name);
            //See if it returns null on a nonexistent vehicle
            $this->assertNull($this->client->findVehicle('DEADBEEF'));
        }

        return $retrievedVehicle;
    }
}
