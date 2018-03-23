<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Factory\DeliveryFactory;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Model\ItemCollection;
use Carbon\Carbon;

class DeliveryFactoryTest extends TestCase
{
    protected $client;
    use CreateClientTrait;

    public function setUp()
    {
        $this->createClient();
    }

    /**
     * Tests if we can create fakes from the Factory.
     *
     * @covers \DeliveryFactory::createFakes
     */
    public function testCreateFakes()
    {
        $newFactory = new DeliveryFactory($this->client);
        $randomNumber = rand(0, 99);
        $newDeliveries = $newFactory->createFakes($randomNumber);
        $this->assertTrue(is_array($newDeliveries));
        //sample one delivery and check
        $sampleDelivery = $newDeliveries[rand(0, count($newDeliveries) - 1)];
        $this->assertEquals("Detrack\DetrackCore\Model\Delivery", get_class($sampleDelivery));
        $this->assertNotNull($sampleDelivery->do);
        $this->assertNotNull($sampleDelivery->date);
        $this->assertNotNull($sampleDelivery->address);
        $this->assertInstanceOf(ItemCollection::class, $sampleDelivery->items);
        $this->assertNotEquals(0, $sampleDelivery->items->count());
    }

    /**
     * Tests if we can create blank deliveries from the factory.
     *
     * @covers \DeliveryFactory::createNew
     */
    public function testCreateNew()
    {
        $newFactory = new DeliveryFactory($this->client);
        $newDelivery = $newFactory->createNew();
        $this->assertInstanceOf(Delivery::class, $newDelivery);
        $newDelivery2 = $newFactory->createNew([
          'date' => Carbon::now()->toDateString(),
          'address' => 'null island',
          'do' => '123',
        ]);
        $this->assertInstanceOf(Delivery::class, $newDelivery2);
        $this->assertEquals(Carbon::now()->toDateString(), $newDelivery2->date);
        $this->assertEquals('null island', $newDelivery2->address);
        $this->assertEquals('123', $newDelivery2->do);
    }
}
