<?php

namespace Detrack\DetrackCore\Client\Traits;

/*
* Dear PHP,
* I love you, but,
* WHY DON'T YOU LET ME USE THE WORD "TRAIT" IN THE NAMESPACE
* WHY PHP WHY
*/
use Detrack\DetrackCore\Model\Delivery;

trait DeliveryMiscActions
{
    /**
     * Sends HTTP request to the View delivery endpoint to find a single delivery.
     *
     * @param array|string $attr An associative array containing the keys "date" and "do" that identifies the delivery, or just the DO to fetch the latest delivery attempt with that DO
     *
     * @return Delivery|null The first delivery that matches the two fields
     */
    public function findDelivery($attr)
    {
        $apiPath = 'deliveries/view.json';
        if (is_string($attr)) {
            $dataArray = ['do' => $attr];
        } else {
            $dataArray = $attr;
        }
        $response = $this->sendData($apiPath, $dataArray);
        $responseObj = json_decode((string) $response->getBody());
        if ($responseObj->info->status != 'ok') {
            //handle errors
        } else {
            if ($responseObj->results[0]->status == 'failed') {
                /* assume the API only returns one error per result */
                if ($responseObj->results[0]->errors[0]->code == Delivery::ERROR_CODE_DELIVERY_NOT_FOUND) {
                    return null;
                }
            }
            $foundDelivery = new Delivery($responseObj->results[0]->delivery);
            //important to reattach the client upon creating the object, or method chaining will fail
            $foundDelivery->setClient($this);

            return $foundDelivery;
        }
    }

    /**
     * Bulk find deliveries.
     *
     * This is similar to DeliveryMiscActions::findDelivery, but does so in only one HTTP request per 100 $deliveries
     * If you pass an array of more than 100 elements, it will break up into separate HTTP requests
     * Use this instead of multiple DeliveryMiscActions::findDelivery() calls to cut down on the number of HTTP requests you have to make
     * Supply an array of Delivery objects or an array of Delivery Indentifier Associative arrays
     * Please use sparingly.
     *
     * @param array $paramArray an array of deliveries or delivery identifiers to findDelivery
     *
     * @return array array of Delivery objects
     */
    public function bulkFindDeliveries($paramArray)
    {
        //standardise array objects into identifiers;
        $paramArray = array_filter(array_map(function ($param) {
            if ($param instanceof Delivery) {
                return $param->getIdentifier();
            } elseif (is_array($param)) {
                if (isset($param['date']) && isset($param['do'])) {
                    return ['date' => $param['date'], 'do' => $param['do']];
                }
            } else {
                return null;
            }
        }, $paramArray));
        //break up into separate requests
        $resultsArray = [];
        $paramArray = array_chunk($paramArray, 100);
        foreach ($paramArray as $paramArrayChunk) {
            $apiPath = 'deliveries/view.json';
            $dataArray = $paramArrayChunk;
            $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
            if ($response->info->status == 'ok') {
                foreach ($response->results as $responseResult) {
                    if ($responseResult->status == 'ok') {
                        array_push($resultsArray, new Delivery(array_filter(json_decode(json_encode($responseResult->delivery), true))));
                    }
                }
            }
        }

        return $resultsArray;
    }

    /**
     * Get all deliveries scheduled for a certain date.
     *
     * @param string $date the date (YYYY-MM-DD) you want to retrieve $deliveries
     *
     * @return array an array of deliveries scheduled for that date
     */
    public function findDeliveriesByDate($date)
    {
        $data = new \stdClass();
        $data->date = $date;
        $apiPath = 'deliveries/view/all.json';
        $response = json_decode((string) $this->sendData($apiPath, $data)->getBody());
        $deliveries = [];
        if ($response->info->status == 'ok') {
            foreach ($response->deliveries as $delivery) {
                array_push($deliveries, new Delivery($delivery));
            }
        }

        return $deliveries;
    }

    /**
     * Bulk save deliveries. This is similar to Delivery::save, but does so in only two HTTP requests per 100 deliveries.
     *
     * It goes through recursion if there are more than 100 deliveries passed in
     * Use this instead of multiple Delivery::save() calls to cut down on the number of HTTP requests you have to make.
     * Supply an array of Delivery objects.
     * It first attempts "create" on every delivery object, collects the ones that failed because it already exists, then calls "update" on the rest.
     * Please use sparingly.
     *
     * @param array $deliveries an array of deliveries to save
     *
     * @return bool|array array of responses containing deliveries that failed either operation for either reason
     */
    public function bulkSaveDeliveries($deliveries)
    {
        //break up into separate requests
        if (count($deliveries) > 100) {
            $this->bulkSaveDeliveries(array_slice(array_values($deliveries), 100));
            $deliveries = array_slice($deliveries, 0, 100);
        }
        $apiPath = 'deliveries/create.json';
        $dataArray = $deliveries;
        $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
        $failedCreates = [];
        $failedEitherWay = [];
        foreach ($response->results as $responseResult) {
            if ($responseResult->status == 'failed') {
                foreach ($responseResult->errors as $error) {
                    if ($error->code == Delivery::ERROR_CODE_DELIVERY_ALREADY_EXISTS) {
                        /*create has failed because it already exists
                         find the Delivery object in the original $dataArray array, and push it to $failedCreates
                         we must push the original delivery object in the $deliveries array instead of the response result,
                         or updates will be lost.
                        */
                        foreach ($deliveries as $deliveriesKey => $delivery) {
                            if (['date' => $responseResult->date, 'do' => $responseResult->do] == $delivery->getIdentifier()) {
                                array_push($failedCreates, $delivery);
                                unset($deliveries[$deliveriesKey]); //unset for faster search next iteration
                            }
                        }
                        array_push($failedCreates, $responseResult);
                    } else {
                        //create failed because of some other reason. ignore.
                        //once again, we must push the original delivery object, or information will be lost.
                        foreach ($deliveries as $deliveriesKey => $delivery) {
                            if (['date' => $responseResult->date, 'do' => $responseResult->do] == $delivery->getIdentifier()) {
                                array_push($failedEitherWay, $delivery);
                                unset($deliveries[$deliveriesKey]); //unset for faster search next iteration
                            }
                        }
                    }
                }
            }
        }
        //if all the creates succeeded, there is no need to try update
        if (count($failedCreates) == 0) {
            return true;
        }
        //now call update
        $apiPath = 'deliveries/update.json';
        $dataArray = $failedCreates;
        $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
        if ($response->info->status != 'ok') {
            throw new \Exception('Something broke:'.json_encode($response));
        }
        if ($response->info->failed != 0) {
            foreach ($response->results as $responseResult) {
                if ($responseResult->status == 'failed') {
                    array_push($failedEitherWay, $responseResult);
                }
            }

            return $failedEitherWay;
        } else {
            return true;
        }
    }

    /**
     * Bulk delete deliveries. This can delete deliveries on different days.
     *
     * Use this instead of multiple Delivery::delete() calls to cut down the number of HTTP requests you have to make.
     * Supply either an array of Delivery objects or an array of associative arrays returned by Delivery::getIdentifier
     * Please use sparingly.
     *
     * @param array $paramArray this can either be an array of Delivery objects, or an array of Delivery identifier associative arrays
     *
     * @return bool|array returns true if all the deletes worked, or a list of delivery identifiers that failed, with an additional index called "errors" that list the errors that occured
     */
    public function bulkDeleteDeliveries($paramArray)
    {
        $apiPath = 'deliveries/delete.json';
        $dataArray = [];
        foreach ($paramArray as $paramElement) {
            if ($paramElement instanceof Delivery) {
                array_push($dataArray, $paramElement->getIdentifier());
            } elseif (is_array($paramElement)) {
                if (count($paramElement) == 2 && array_key_exists('date', $paramElement) && array_key_exists('do', $paramElement)) {
                    array_push($dataArray, $paramElement);
                } else {
                    //bad element, dont do anything
                }
            }
        }
        $failedDeletes = [];
        $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
        if ($response->info->status != 'ok') {
            //handle errors
            return false;
        } else {
            foreach ($response->results as $responseResult) {
                if ($responseResult->status != 'ok') {
                    array_push($failedDeletes, new Delivery($responseResult));
                }
            }
        }
        if (count($failedDeletes) != 0) {
            return $failedDeletes;
        } else {
            return true;
        }
    }

    /**
     * Delete all deliveries scheduled for a certain date.
     *
     * This may take some time, so please use sparingly
     *
     * @param string $date the date of which you want to delete all deliveries
     *
     * @return bool|int returns true if all the deliveries were deleted successfully, or the number of failed deletes. Returns false if something else broke.
     */
    public function deleteDeliveriesByDate($date)
    {
        $data = new \stdClass();
        $data->date = $date;
        $apiPath = 'deliveries/delete/all.json';
        $response = json_decode((string) $this->sendData($apiPath, $data)->getBody());
        if ($response->info->status == 'ok') {
            if ($response->info->failed == 0) {
                return true;
            } else {
                return $response->info->failed;
            }
        } else {
            return false;
        }
    }
}
