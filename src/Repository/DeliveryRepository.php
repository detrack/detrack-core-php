<?php

namespace Detrack\DetrackCore\Repository;

use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Repository\Exception\MissingFieldException;
use Detrack\DetrackCore\Factory\Exception\NoClientAttachedException;
use Detrack\DetrackCore\Model\Delivery;

trait DeliveryRepository
{
    use Repository;

    /**
     * Saves the Delivery model by sending a HTTP request to the API, with the key registered to the client attached to this model.
     *
     * Actually, it tries to see if a delivery already exists, then it chooses whether to send to the Edit API or the Create API.
     */
    public function save(DetrackClient $client = null)
    {
        if ($client == null && $this->client == null) {
            throw new NoClientAttachedException('No client attached');
        }
        if ($client != null && $this->client == null) {
            $this->client = $client;
        }
        if ($this->client->findDelivery($this->getIdentifier()) != null) {
            $this->update();
        } else {
            static::create($this);
        }
        $this->resetModifiedAttributes();
    }

    /**
     * Sends HTTP request to the create new delivery endpoint.
     *
     * @throws MissingFieldException if the required fields are not present
     */
    private static function create($delivery)
    {
        $apiPath = 'deliveries/create.json';
        $dataArray = $delivery->attributes;
        //check for required fields;
        if ($dataArray['date'] == null) {
            throw new MissingFieldException('Delivery', 'date');
        } elseif ($dataArray['do'] == null) {
            throw new MissingFieldException('Delivery', 'do');
        } elseif ($dataArray['address'] == null) {
            throw new MissingFieldException('Delivery', 'address');
        }
        $response = $delivery->client->sendData($apiPath, $dataArray);
        $responseObj = json_decode((string) $response->getBody());
        if ($responseObj->results[0]->status != 'ok') {
            var_dump($responseObj);
            var_dump($dataArray);
            throw new \Exception('Failed to create new delivery');
        }

        return $responseObj;
    }

    /**
     * Sends HTTP request to the edit delivery endpoint.
     *
     * This is for editing a single delivery only.
     *
     * @throws MissingFieldException if the required fields are not present
     */
    private function update()
    {
        $apiPath = 'deliveries/update.json';
        $dataArray = $this->attributes;
        if ($dataArray['date'] == null) {
            throw new MissingFieldException('Delivery', 'date');
        } elseif ($dataArray['do'] == null) {
            throw new MissingFieldException('Delivery', 'do');
        } elseif ($dataArray['address'] == null) {
            throw new MissingFieldException('Delivery', 'address');
        }
        $response = $this->client->sendData($apiPath, $dataArray);
        $responseObj = json_decode((string) $response->getBody());
        if ($responseObj->results[0]->status != 'ok') {
            var_dump($responseObj);
            var_dump($dataArray);
            throw new \Exception('Failed to update delivery');
        }

        return $responseObj;
    }

    /**
     * Sends HTTP request to the delete deliveries endpoint.
     *
     * This is for deleting a single delivery object only.
     *
     * @throws MissingFieldException if date or do fields are somehow missing from the object
     *
     * @return bool true if delete was successful, false if apache_note
     */
    public function delete()
    {
        $apiPath = 'deliveries/delete.json';
        $dataArray = $this->getIdentifier();
        if ($dataArray['date'] == null) {
            throw new MissingFieldException('Delivery', 'date');
        } elseif ($dataArray['do'] == null) {
            throw new MissingFieldException('Delivery', 'do');
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
