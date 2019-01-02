<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Resource\Job;
use Detrack\DetrackCore\Model\Item;

final class ItemTest extends TestCase
{
    public const TEST_JOB_DO = 'DCPHPv2_'.self::class;
    public const TEST_JOB_ADDRESS = 'PHP Island';
    private static $seed;

    public static function setUpBeforeClass(): void
    {
        self::$seed = rand();
    }

    public function setUp(): void
    {
        $this->testJob = new Job([
            'do_number' => self::TEST_JOB_DO.'_'.$this->getName().'_'.self::$seed,
            'address' => self::TEST_JOB_ADDRESS,
            'date' => date('Y-m-d'),
        ]);
    }

    public function tearDown(): void
    {
        $this->testJob->delete();
    }

    public function testCanSaveWithSkuOnly(): void
    {
        $testItem = new Item([
            'sku' => '12345',
        ]);
        $this->testJob->items->add($testItem);
        $this->testJob->save();
        $this->assertEquals(1, $this->testJob->items_count);
    }

    public function testCannotSaveWithoutSku(): void
    {
        $testItem = new Item([
            'qty' => 1,
        ]);
        $this->testJob->items->add($testItem);
        $this->testJob->save();
        $this->assertEquals(0, $this->testJob->items_count);
    }

    public function testCanSaveWithDescOnly(): void
    {
        $testItem = new Item([
            'description' => 'Some item',
        ]);
        $this->testJob->items->add($testItem);
        $this->testJob->save();
        $this->assertEquals(1, $this->testJob->items_count);
    }

    public function testQtyCanBeInteger(): void
    {
        $testItem = new Item([
            'description' => 'Some item',
            'quantity' => 1,
        ]);
        $this->testJob->items->add($testItem);
        $this->testJob->save();
        $this->assertEquals(1, $this->testJob->items_count);
        $this->assertEquals(1, $this->testJob->items[0]->quantity);
    }

    /** Tests that float values of the quantity field of \Detrack\DetrackCore\Model\Item will be casted to an integer
     *
     */
    public function testFloatQuantitiesWillBeConvertedToInteger(): void
    {
        $testItem = new Item([
            'description' => 'Some item',
            'quantity' => 1.5,
        ]);
        $this->testJob->items->add($testItem);
        $this->testJob->save();
        $this->assertEquals(1, $this->testJob->items_count);
        $this->assertNotEquals(1.5, $this->testJob->items[0]->quantity);
        $this->assertEquals(1, $this->testJob->items[0]->quantity);
    }

    /** Tests that numeric string values of the quantity field of \Detrack\DetrackCore\Model\Item will be casted to an integer
     *
     */
    public function testStringQuantitiesWillBeConvertedToInteger(): void
    {
        $testItem = new Item([
            'description' => 'Some item',
            'quantity' => '1.5',
        ]);
        $this->testJob->items->add($testItem);
        $this->testJob->save();
        $this->assertEquals(1, $this->testJob->items_count);
        $this->assertNotEquals('1.5', $this->testJob->items[0]->quantity);
        $this->assertEquals(1, $this->testJob->items[0]->quantity);
    }

    /** Tests that invalid string (contains non-numeric characters other than `'.'`) values of the quantity field of \Detrack\DetrackCore\Model\Item will be casted to `0`
     *
     */
    public function testBadStringQuantitiesWillBeConvertedToZero(): void
    {
        $testItem = new Item([
            'description' => 'Some item',
            'quantity' => 'Nani the fluff',
        ]);
        $this->testJob->items->add($testItem);
        $this->testJob->save();
        $this->assertEquals(1, $this->testJob->items_count);
        $this->assertNotEquals('Nani the fluff', $this->testJob->items[0]->quantity);
        $this->assertEquals(0, $this->testJob->items[0]->quantity);
    }
}
