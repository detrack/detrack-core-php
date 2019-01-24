<?php

namespace Detrack\DetrackCore\Repository;

use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Repository\Exception\MissingFieldException;
use Detrack\DetrackCore\Factory\Exception\NoClientAttachedException;

trait CollectionRepository
{
    use Repository;

    /**
     * Saves the Collection model by sending a HTTP request to the API, with the key registered to the client attached to this model.
     *
     * Actually, it tries to see if a collection already exists, then it chooses whether to send to the Edit API or the Create API.
     */
    public function save(DetrackClient $client = null)
    {
        if ($client == null && $this->client == null) {
            throw new NoClientAttachedException('No client attached');
        }
        if ($client != null && $this->client == null) {
            $this->client = $client;
        }
        if ($this->date == null) {
            $foundCollection = $this->client->findCollection($this->do);
            if ($foundCollection != null) {
                $this->date = $foundCollection->date;
                $this->update();
            } else {
                $this->date = date('Y-m-d');
                static::create($this);
            }
        } else {
            if ($this->client->findCollection($this->getIdentifier()) != null) {
                $this->update();
            } else {
                static::create($this);
            }
        }
        $this->resetModifiedAttributes();
    }

    /**
     * Sends HTTP request to the create new collection endpoint.
     *
     * @param mixed $collection
     *
     * @throws MissingFieldException if the required fields are not present
     */
    private static function create($collection)
    {
        $apiPath = 'collections/create.json';
        $dataArray = $collection->jsonSerialize();
        //check for required fields;
        if ($dataArray['date'] == null) {
            throw new MissingFieldException('Collection', 'date');
        } elseif ($dataArray['do'] == null) {
            throw new MissingFieldException('Collection', 'do');
        } elseif ($dataArray['address'] == null) {
            throw new MissingFieldException('Collection', 'address');
        }
        $response = $collection->client->sendData($apiPath, $dataArray);
        $responseObj = json_decode((string) $response->getBody());
        if ($responseObj->results[0]->status != 'ok') {
            throw new \Exception('Failed to create new collection, '.var_export($responseObj, true).' , '.var_export($dataArray, true));
        }

        return $responseObj;
    }

    /**
     * Sends HTTP request to the edit collection endpoint.
     *
     * This is for editing a single collection only.
     *
     * @throws MissingFieldException if the required fields are not present
     */
    private function update()
    {
        $apiPath = 'collections/update.json';
        $dataArray = $this->jsonSerialize();
        if ($dataArray['date'] == null) {
            throw new MissingFieldException('Collection', 'date');
        } elseif ($dataArray['do'] == null) {
            throw new MissingFieldException('Collection', 'do');
        } elseif ($dataArray['address'] == null) {
            throw new MissingFieldException('Collection', 'address');
        }
        $response = $this->client->sendData($apiPath, $dataArray);
        $responseObj = json_decode((string) $response->getBody());
        if ($responseObj->results[0]->status != 'ok') {
            throw new \Exception('Failed to update collection, '.var_export($responseObj, true).' , '.var_export($dataArray, true));
        }

        return $responseObj;
    }

    /**
     * Sends HTTP request to the delete collections endpoint.
     *
     * This is for deleting a single collection object only.
     *
     * @throws MissingFieldException if date or do fields are somehow missing from the object
     *
     * @return bool true if delete was successful, false if apache_note
     */
    public function delete()
    {
        $apiPath = 'collections/delete.json';
        $dataArray = $this->getIdentifier();
        if ($dataArray['date'] == null) {
            throw new MissingFieldException('Collection', 'date');
        } elseif ($dataArray['do'] == null) {
            throw new MissingFieldException('Collection', 'do');
        }
        $response = $this->client->sendData($apiPath, $dataArray);
        $responseObj = json_decode((string) $response->getBody());
        if ($responseObj->info->failed != 0) {
            return false;
        } else {
            return true;
        }
    }
}
