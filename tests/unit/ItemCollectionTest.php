<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Model\Item;
use Detrack\DetrackCore\Model\ItemCollection;

/**
 * Extra tests for testing POD features. You must fill up the relevant .env fields for these to work.
 */
class ItemCollectionTest extends TestCase
{
    private $testCollection;

    public function setUp()
    {
        $this->testCollection = new ItemCollection();
    }

    /**
     * Tests if we can push an item into the collection.
     *
     * @covers \ItemCollection::push
     */
    public function testPush()
    {
        $testCollection = $this->testCollection;
        for ($i = 0; $i < 10; ++$i) {
            $sampleItem = new Item([
                'sku' => rand(0, 999999),
                'desc' => 'testing item '.$i,
                'qty' => rand(0, 10),
            ]);
            $testCollection->push($sampleItem);
            $this->assertEquals($i + 1, $testCollection->count());
        }

        return $testCollection;
    }

    /**
     * Tests if we can get the first item.
     *
     * @covers \ItemCollection::first
     * @depends testPush
     */
    public function testFirst($testCollection)
    {
        $firstItem = $testCollection->first();
        $this->assertEquals('testing item 0', $firstItem->desc);

        return $testCollection;
    }

    /**
     * Tests if we can get the last item.
     *
     * @covers \ItemCollection::last
     * @depends testFirst
     */
    public function testLast($testCollection)
    {
        $lastItem = $testCollection->last();
        $this->assertEquals('testing item '.($testCollection->count() - 1), $lastItem->desc);

        return $testCollection;
    }

    /**
     * Tests if we can retrieve items at arbitary positions.
     *
     * @covers \ItemCollection::at
     * @depends testLast
     */
    public function testAt($testCollection)
    {
        for ($i = 0; $i < $testCollection->count(); ++$i) {
            $sampleItem = $testCollection->at($i);
            $this->assertEquals('testing item '.$i, $sampleItem->desc);
        }

        return $testCollection;
    }

    /**
     * Tests if we can remove at element at a specific indexes.
     *
     * @covers \ItemCollection::removeAt
     * @depends testAt
     */
    public function testRemoveAt($testCollection)
    {
        $index = rand(0, $testCollection->count() - 2); //dont remove the last one
        $originalElement = $testCollection->at($index);
        $testCollection->removeAt($index);
        $this->assertNotNull($testCollection->at($index));
        $this->assertNotEquals($originalElement, $testCollection->at($index));

        return $testCollection;
    }

    /**
     * Tests if we can pop the last item in the collection.
     *
     * @covers \ItemCollection::pop
     * @depends testRemoveAt
     */
    public function testPop($testCollection)
    {
        $originalLength = $testCollection->count();
        $amountToRemove = rand(2, $originalLength);
        for ($i = 0; $i < $amountToRemove; ++$i) {
            $testCollection->pop();
            $this->assertEquals($originalLength - $i - 1, $testCollection->count());
        }
        $this->assertEquals($originalLength - $amountToRemove, $testCollection->count());

        return $testCollection;
    }
}
