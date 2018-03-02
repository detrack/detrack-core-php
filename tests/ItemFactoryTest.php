<?php

require_once "CreateClientTrait.php";

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Model\Item;
use Detrack\DetrackCore\Model\ItemCollection;
use Detrack\DetrackCore\Factory\ItemFactory;
/**
* Extra tests for testing POD features. You must fill up the relevant .env fields for these to work.
*/
class ItemFactoryTest extends TestCase{
  /**
  * Tests if we can create fakes, non-statically
  *
  * @covers ItemFactory::createFakes
  */
  public function testCreateFakes(){
    $n = rand(0,100);
    $itemFactory = new ItemFactory();
    $sampleItems = $itemFactory->createFakes($n);
    $this->assertInstanceOf(ItemCollection::class,$sampleItems);
    $this->assertEquals($n,$sampleItems->count());
    foreach($sampleItems as $key=>$sampleItem){
      $this->assertInstanceOf(Item::class,$sampleItem);
      $this->assertEquals("testing item " . $key,$sampleItem->desc);
    }
  }
  /**
  * Tests if we can create fakes, statically
  *
  * @covers ItemFactory::fakes
  */
  public function testFakes(){
    $n = rand(0,100);
    $sampleItems = ItemFactory::fakes($n);
    $this->assertInstanceOf(ItemCollection::class,$sampleItems);
    $this->assertEquals($n,$sampleItems->count());
    foreach($sampleItems as $key=>$sampleItem){
      $this->assertInstanceOf(Item::class,$sampleItem);
      $this->assertEquals("testing item " . $key,$sampleItem->desc);
    }
  }
}

?>
