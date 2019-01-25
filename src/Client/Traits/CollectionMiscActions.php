<?php

namespace Detrack\DetrackCore\Client\Traits;

/*
* Dear PHP,
* I love you, but,
* WHY DON'T YOU LET ME USE THE WORD "TRAIT" IN THE NAMESPACE
* WHY PHP WHY
*/
use Detrack\DetrackCore\Model\Collection;

trait CollectionMiscActions
{
    /**
     * Sends HTTP request to the View collection endpoint to find a single collection.
     *
     * @param array|string $attr An associative array containing the keys "date" and "do" that identifies the collection, or just the DO to fetch the latest collection attempt with that DO
     *
     * @return Collection|null The first collection that matches the two fields
     */
    public function findCollection($attr)
    {
        $apiPath = 'collections/view.json';
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
                if ($responseObj->results[0]->errors[0]->code == Collection::ERROR_CODE_COLLECTION_NOT_FOUND) {
                    return null;
                }
            }
            $foundCollection = new Collection($responseObj->results[0]->collection);
            //important to reattach the client upon creating the object, or method chaining will fail
            $foundCollection->setClient($this);

            return $foundCollection;
        }
    }

    /**
     * Bulk find collections.
     *
     * This is similar to CollectionMiscActions::findCollection, but does so in only one HTTP request per 100 $collections
     * If you pass an array of more than 100 elements, it will break up into separate HTTP requests
     * Use this instead of multiple CollectionMiscActions::findCollection() calls to cut down on the number of HTTP requests you have to make
     * Supply an array of Collection objects or an array of Collection Indentifier Associative arrays
     * Please use sparingly.
     *
     * @param array $paramArray an array of collections or collection identifiers to findCollection
     *
     * @return array array of Collection objects
     */
    public function bulkFindCollections($paramArray)
    {
        //standardise array objects into identifiers;
        $paramArray = array_filter(array_map(function ($param) {
            if ($param instanceof Collection) {
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
            $apiPath = 'collections/view.json';
            $dataArray = $paramArrayChunk;
            $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
            if ($response->info->status == 'ok') {
                foreach ($response->results as $responseResult) {
                    if ($responseResult->status == 'ok') {
                        array_push($resultsArray, new Collection(array_filter(json_decode(json_encode($responseResult->collection), true))));
                    }
                }
            }
        }

        return $resultsArray;
    }

    /**
     * Get all collections scheduled for a certain date.
     *
     * @param string $date the date (YYYY-MM-DD) you want to retrieve $collections
     *
     * @return array an array of collections scheduled for that date
     */
    public function findCollectionsByDate($date)
    {
        $data = new \stdClass();
        $data->date = $date;
        $apiPath = 'collections/view/all.json';
        $response = json_decode((string) $this->sendData($apiPath, $data)->getBody());
        $collections = [];
        if ($response->info->status == 'ok') {
            foreach ($response->collections as $collection) {
                array_push($collections, new Collection($collection));
            }
        }

        return $collections;
    }

    /**
     * Bulk create collections. Strictly creates only, if a certain collection already exists it will be returned in an array of failed creates.
     *
     * @param array $collections an array of collections to create
     *
     * @return array array of responses containing collections that failed either operation for either reason
     */
    public function bulkCreateCollections($collections)
    {
        $failedCreates = [];
        //break up into separate requests
        if (count($collections) > 100) {
            $recurseResult = $this->bulkCreateCollections(array_slice(array_values($collections), 100));
            if (is_array($recurseResult)) {
                $failedCreates = array_merge($failedCreates, $recurseResult);
            }
            $collections = array_slice($collections, 0, 100);
        }
        $apiPath = 'collections/create.json';
        $dataArray = $collections;
        $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
        foreach ($response->results as $responseResult) {
            if ($responseResult->status == 'failed') {
                array_push($failedCreates, $responseResult);
            }
        }

        return $failedCreates;
    }

    /**
     * Bulk update collections. Strictly updates only, if a certain collection does not already exist it will be returned in an array of failed updates.
     *
     * @param array $collections an array of collections to update
     *
     * @return array array of responses containing collections that failed either operation for either reason
     */
    public function bulkUpdateCollections($collections)
    {
        $failedUpdates = [];
        //break up into separate requests
        if (count($collections) > 100) {
            $recurseResult = $this->bulkUpdateCollections(array_slice(array_values($collections), 100));
            if (is_array($recurseResult)) {
                $failedUpdates = array_merge($failedUpdates, $recurseResult);
            }
            $collections = array_slice($collections, 0, 100);
        }
        $apiPath = 'collections/update.json';
        $dataArray = $collections;
        $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
        if ($response->info->status != 'ok') {
            throw new \Exception('Something broke:'.json_encode($response));
        }
        if ($response->info->failed != 0) {
            foreach ($response->results as $responseResult) {
                if ($responseResult->status == 'failed') {
                    array_push($failedUpdates, $responseResult);
                }
            }

            return $failedUpdates;
        }
    }

    /**
     * Bulk save collections. This is similar to Collection::save, but does so in only two HTTP requests per 100 collections.
     *
     * It goes through recursion if there are more than 100 collections passed in
     * Use this instead of multiple Collection::save() calls to cut down on the number of HTTP requests you have to make.
     * Supply an array of Collection objects.
     * It first attempts "create" on every collection object, collects the ones that failed because it already exists, then calls "update" on the rest.
     * Please use sparingly.
     *
     * @param array $collections an array of collections to save
     *
     * @return bool|array array of responses containing collections that failed either operation for either reason
     */
    public function bulkSaveCollections($collections)
    {
        $failedEitherWay = [];
        //break up into separate requests
        if (count($collections) > 100) {
            $recurseResult = $this->bulkSaveCollections(array_slice(array_values($collections), 100));
            if (is_array($recurseResult)) {
                $failedEitherWay = array_merge($failedEitherWay, $recurseResult);
            }
            $collections = array_slice($collections, 0, 100);
        }
        $apiPath = 'collections/create.json';
        $dataArray = $collections;
        $response = json_decode((string) $this->sendData($apiPath, $dataArray)->getBody());
        $failedCreates = [];
        foreach ($response->results as $responseResult) {
            if ($responseResult->status == 'failed') {
                foreach ($responseResult->errors as $error) {
                    if ($error->code == Collection::ERROR_CODE_COLLECTION_ALREADY_EXISTS) {
                        /*create has failed because it already exists
                         find the Collection object in the original $dataArray array, and push it to $failedCreates
                         we must push the original collection object in the $collections array instead of the response result,
                         or updates will be lost.
                        */
                        foreach ($collections as $collectionsKey => $collection) {
                            if (['date' => $responseResult->date, 'do' => $responseResult->do] == $collection->getIdentifier()) {
                                array_push($failedCreates, $collection);
                                unset($collections[$collectionsKey]); //unset for faster search next iteration
                            }
                        }
                        array_push($failedCreates, $responseResult);
                    } else {
                        //create failed because of some other reason. ignore.
                        //once again, we must push the original collection object, or information will be lost.
                        foreach ($collections as $collectionsKey => $collection) {
                            if (['date' => $responseResult->date, 'do' => $responseResult->do] == $collection->getIdentifier()) {
                                array_push($failedEitherWay, $collection);
                                unset($collections[$collectionsKey]); //unset for faster search next iteration
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
        $apiPath = 'collections/update.json';
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
     * Bulk delete collections. This can delete collections on different days.
     *
     * Use this instead of multiple Collection::delete() calls to cut down the number of HTTP requests you have to make.
     * Supply either an array of Collection objects or an array of associative arrays returned by Collection::getIdentifier
     * Please use sparingly.
     *
     * @param array $paramArray this can either be an array of Collection objects, or an array of Collection identifier associative arrays
     *
     * @return bool|array returns true if all the deletes worked, or a list of collection identifiers that failed, with an additional index called "errors" that list the errors that occured
     */
    public function bulkDeleteCollections($paramArray)
    {
        $apiPath = 'collections/delete.json';
        $dataArray = [];
        foreach ($paramArray as $paramElement) {
            if ($paramElement instanceof Collection) {
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
                    array_push($failedDeletes, new Collection($responseResult));
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
     * Delete all collections scheduled for a certain date.
     *
     * This may take some time, so please use sparingly
     *
     * @param string $date the date of which you want to delete all collections
     *
     * @return bool|int returns true if all the collections were deleted successfully, or the number of failed deletes. Returns false if something else broke.
     */
    public function deleteCollectionsByDate($date)
    {
        $data = new \stdClass();
        $data->date = $date;
        $apiPath = 'collections/delete/all.json';
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
