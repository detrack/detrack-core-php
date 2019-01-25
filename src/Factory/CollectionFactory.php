<?php

namespace Detrack\DetrackCore\Factory;

use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Factory\Exception\NoClientAttachedException;
use Detrack\DetrackCore\Model\Collection;

class CollectionFactory extends Factory
{
    public function __construct(DetrackClient $client = null)
    {
        if ($client == null) {
            if (static::$defaultClient != null) {
                $client = static::$defaultClient;
            } else {
                throw new NoClientAttachedException('No client passed in factory constructor, or no default client set');
            }
        } elseif (!($client instanceof DetrackClient)) {
            throw new NoClientAttachedException('Object passed in constructor is not an instance of DetrackClient');
        }
        $this->client = $client;
    }

    /**
     * Creates one more many collection objects with the client and fake data automatically set.
     *
     * @param int specify how many to create
     * @param mixed $num
     *
     * @return array an array of fake collections
     */
    public function createFakes($num = 1)
    {
        $newArray = [];
        for ($i = 0; $i < $num; ++$i) {
            $newCollection = new Collection([
                'date' => \Carbon\Carbon::now()->toDateString(),
                'do' => rand(0, 99999999999).'-'.\Carbon\Carbon::now()->toTimeString(),
                'address' => 'Null island',
                'items' => ItemFactory::fakes(rand(1, 10)),
            ]);
            $newCollection->setClient($this->client);
            array_push($newArray, $newCollection);
        }

        return $newArray;
    }

    /**
     * Create a new collection object, either blank or filled with whatever the user gave.
     *
     * @param array $attr attributes you want to pass to the new collection
     *
     * @return Collection the collection object
     */
    public function createNew($attr = [])
    {
        $newCollection = new Collection($attr);
        $newCollection->setClient($this->client);

        return $newCollection;
    }
}
