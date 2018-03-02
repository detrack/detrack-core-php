<?php

namespace Detrack\DetrackCore\Factory;

use Detrack\DetrackCore\Factory\Factory;
use Detrack\DetrackCore\Model\Item;
use Detrack\DetrackCore\Model\ItemCollection;

class ItemFactory extends Factory{
  /**
  * Creates one more many item objects with fake data automatically set
  *
  * Item description will always be "test item {{$i}}"
  *
  * @param Integer specify how many to create
  *
  * @return ItemCollection an ItemCollectionof fake items
  */
  public function createFakes($num=1){
    $itemCollection = new ItemCollection();
    for($i=0;$i<$num;$i++){
      $sampleItem = new Item([
        "sku" => rand(0,999999),
        "desc" => "testing item " . $i,
        "qty" => rand(0,10),
      ]);
      $itemCollection->push($sampleItem);
    }
    return $itemCollection;
  }
  /**
  * Static version of createFakes
  *
  * @see DelvieryFactory::createFakes
  *
  * @param Integer specify how many to create
  *
  * @return ItemCollection an ItemCollectionof fake items
  */
  public static function fakes($num=1){
    $itemCollection = new ItemCollection();
    for($i=0;$i<$num;$i++){
      $sampleItem = new Item([
        "sku" => rand(0,999999),
        "desc" => "testing item " . $i,
        "qty" => rand(0,10),
      ]);
      $itemCollection->push($sampleItem);
    }
    return $itemCollection;
  }
}


 ?>
