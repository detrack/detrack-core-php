<?php

namespace Detrack\DetrackCore\Model;

use Detrack\DetrackCore\Model\Item;

/**
* Actually not a model, but I'll leave it here for now
* And actually operates more like a stack......
*/
class ItemCollection extends \ArrayObject implements \JsonSerializable{
  /**
  * The constructor. Casts the supplied arrays to Item objects
  *
  * @param array $attr attributes of the items you want to initialise
  *
  */
  public function __construct($attr=NULL){
    if(is_array($attr)){
      for($i=0;$i<count($attr);$i++){
        if(is_array($attr[$i])){
          $attr[$i] = new Item($attr[$i]);
        }else if(!($attr[$i] instanceof Item)){
          unset($attr[$i]);
        }
      }
      parent::__construct(array_values($attr));
    }
  }
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
    return $this->removeAt($this->count()-1);
  }
  /**
  * Removes element at given position
  *
  * @param int $pos position you want to remove
  */
  public function removeAt($pos){
    $iterator = $this->getIterator();
    $iterator->seek($pos);
    $this->offsetUnset($iterator->key());
    return $this->resetKeys();
  }
  /**
  * Reset the keys of this collection when a destructive method is called
  *
  */
  private function resetKeys(){
    return new ItemCollection(array_values($this->getArrayCopy()));
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
  /**
  * Return attributes that PHP's json_encode will act on
  *
  * Because the API will treat values entered as NULL as deleting, we will remove null values except where it was modified
  * Call json_encode on each Item object
  *
  * @return Array the model's array attributes
  */
  public function jsonSerialize(){
    return $this->getArrayCopy();
  }
  /**
  * Defines __toString() magic method for debugging purposes
  *
  * For now, calls json_encode on itself (and thus jsonSerialize()).
  *
  * @return String String representation of the model
  */
  public function __toString(){
    return json_encode($this);
  }
}

 ?>
