<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Model\Item;

/**
* Actually not a model, but I'll leave it here for now
* And actually operates more like a stack......
*/
class ItemCollection extends \ArrayObject{
  /**
  * Pushes an item to the end of the collection
  *
  * @param Item $item the item to add
  *
  * @return ItemCollection returns itself for method chaining
  */
  public function push(Item $item){
    return $this->append($item);
  }
  /**
  * Add an item to the end of the collection. Alias of push.
  *
  * @see Item::push
  *
  * @param Item $item the item to ad
  *
  * @return ItemCollection returns itself for method chaining
  */
  public function add(Item $item){
    return $this->push($item);
  }
  /**
  * Pops the item at the end of the collection
  *
  * @throws OutOfBoundsException if you attempt to pop an empty collection
  *
  * @return ItemCollection returns itself for method chaining
  */
  public function pop(){
    $iterator = $this->getIterator();
    $iterator->seek($this->count()-1);
    return $this->offsetUnset($iterator->key());
  }
  /**
  * Gets the item at the specified index
  *
  * @param int $index the index you want to retrieve the value from
  *
  * @return Item|NULL the item at the specified index, null if there is nothing
  */
  public function at(int $index){
    $iterator = $this->getIterator();
    $iterator->seek($index);
    return $this->offsetGet($iterator->key());
  }
  /**
  * Gets the first item in the collection
  *
  * @return Item|NULL the first item in the collection, null if there is nothing
  *
  */
  public function first(){
    if($this->count()==0){
      return NULL;
    }
    $iterator = $this->getIterator();
    $iterator->seek(0);
    return $this->offsetGet($iterator->key());
  }
  /**
  * Gets the last item in the collection
  *
  * @return Item|NULL the last item in the collection, null if there is nothing
  */
  public function last(){
    $iterator = $this->getIterator();
    $iterator->seek($this->count()-1);
    return $this->offsetGet($iterator->key());
  }
}

 ?>
