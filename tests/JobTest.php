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

    /**
     * Tests downloading the docs.
     * Will test pdf/tiff, and all three different target scenarios.
     */
    public function testDownloadDoc()
    {
        if (getenv('SAMPLE_DELIVERY_DO') == null) {
            $this->markTestSkipped('Sample delivery DO not found in .env. Refer to .env.example for details.');

            return;
        }
        $testJob = new Job();
        $testJob->do_number = getenv('SAMPLE_DELIVERY_DO');
        $testJob = $testJob->get();
        $tmpDir = sys_get_temp_dir();
        if (!is_writable($tmpDir)) {
            $this->markTestSkipped('Tmp directory ('.$tmpDir.')not writable, please check permissions');

            return;
        }
        $formatTestCases = ['pdf', 'tiff'];
        $formatExpectedResults = ['application/pdf', 'image/tiff'];
        $saveDir = $tmpDir.DIRECTORY_SEPARATOR.'detrack-core-dev';
        if (!file_exists($saveDir) && !is_dir($saveDir)) {
            mkdir($saveDir);
        }
        $targetTestCases = [$saveDir, $saveDir.DIRECTORY_SEPARATOR.'test'.$testJob->id.'.', null];
        for ($i = 0; $i < count($formatTestCases); ++$i) {
            for ($j = 0; $j < count($targetTestCases); ++$j) {
                $returnValue = $testJob->downloadDoc('pod', $formatTestCases[$i], $targetTestCases[$j]);
                //assert return values
                if ($targetTestCases[$j] == null) {
                    $this->assertNotNull($returnValue);
                } else {
                    $this->assertInternalType('bool', $returnValue);
                    $this->assertTrue($returnValue);
                }
                //assert file types
                if ($j == 0) {
                    $command = 'ls -1t '.$targetTestCases[$j].' | head -n 1 | xargs find '.$saveDir.' -name';
                    $targetTestFile = trim(shell_exec($command));
                } elseif ($j == 1) {
                    $targetTestFile = $targetTestCases[$j];
                } elseif ($j == 2) {
                    $targetTestFile = $saveDir.DIRECTORY_SEPARATOR.time().'.'.$formatTestCases[$i];
                    file_put_contents($targetTestFile, $returnValue);
                }
                $this->assertEquals($formatExpectedResults[$i], mime_content_type($targetTestFile));
            }
        }
        shell_exec('rm -r '.$saveDir);
    }
}
