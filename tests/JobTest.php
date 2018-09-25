<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClientStatic;
use Detrack\DetrackCore\Resource\Job;
use Detrack\DetrackCore\Resource\Model\Item;

final class JobTest extends TestCase
{
    public function testSaveJob()
    {
        $testJob = new Job();
        $testJob->address = 'PHP Island';
        $newDo = 'PHP DCv2 - '.time();
        $testJob->do_number = $newDo;
        $testJob->date = date('Y-m-d');
        $testJob->save();
        $response = DetrackClientStatic::sendData('GET', 'jobs/'.$testJob->id, []);
        $this->assertEquals($newDo, $response->data->do_number);

        return $testJob;
    }

    /**
     * @depends testSaveJob
     */
    public function testDeleteJob($testJob)
    {
        $testJob->delete();
        $response = DetrackClientStatic::sendData('GET', 'jobs/'.$testJob->id, []);
        $this->assertEquals('not_found', $response->code);
    }

    public function testSaveJobWithItems()
    {
        $testJob = new Job();
        $testJob->address = 'PHP Island';
        $newDo = 'PHP DCv2 - '.time();
        $testJob->do_number = $newDo;
        $testJob->date = date('Y-m-d');
        $newItem = new Item();
        $newItem->sku = 'RuntimeException';
        $newItem->description = 'You messed up bro';
        $newItem->quantity = 1;
        $testJob->items->add($newItem);
        $testJob->save();
        $response = DetrackClientStatic::sendData('GET', 'jobs/'.$testJob->id, []);
        $this->assertEquals($newDo, $response->data->do_number);
        $this->assertEquals('RuntimeException', $response->data->items[0]->sku);
    }
}
