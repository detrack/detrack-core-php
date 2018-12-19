<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClientStatic;
use Detrack\DetrackCore\Resource\Job;
use Detrack\DetrackCore\Model\Item;

final class JobTest extends TestCase
{
    public function testSaveJob()
    {
        $testJob = new Job();
        $testJob->address = 'PHP Island';
        $newDo = 'PHP DCv2 - '.rand();
        $testJob->do_number = $newDo;
        $testJob->date = date('Y-m-d');
        $testJob->save();
        $response = DetrackClientStatic::sendData('GET', 'jobs/'.$testJob->id, []);
        $this->assertEquals($newDo, $response->data->do_number);
        $testJob->address = 'PHP Islet';
        $testJob->save();
        $response = DetrackClientStatic::sendData('GET', 'jobs/'.$testJob->id, []);
        $this->assertEquals($testJob->address, $response->data->address);

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
        $newDo = 'PHP DCv2 - '.rand();
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
        $testJob->delete();
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
        $testJob = $testJob->hydrate();
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
        $testJob->delete();
    }

    /**
     * Tests bulk search/list jobs.
     *
     * @covers \Detrack\DetrackCore\Resource\Job::listJobs
     */
    public function testListJobs()
    {
        $testJobs = [];
        $seed = rand();
        for ($i = 0; $i < 3; ++$i) {
            $testJob = new Job();
            $testJob->do_number = 'DCPHPv2_'.rand().'_'.$i;
            $testJob->date = date('Y-m-d');
            $testJob->address = 'PHP Islet No.'.$seed;
            $testJob->save();
            array_push($testJobs, $testJob);
        }
        for ($i = 0; $i < 10; ++$i) {
            $returnedJobs = Job::listJobs(
              [
                  'date' => date('Y-m-d'),
                  'sort' => 'created_at',
              ],
              'PHP Islet No.'.$seed
            );
            if (count($returnedJobs) == 3) {
                break;
            }
            if ($i == 9) {
                $this->markTestFailed('Unable to get 3 Jobs created in time');
            }
        }
        $this->assertEquals(3, count($returnedJobs));
        foreach ($testJobs as $testJob) {
            $found = false;
            foreach ($returnedJobs as $returnedJob) {
                if ($testJob->id == $returnedJob->id) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(true, $found);
        }
        foreach ($testJobs as $testJob) {
            $testJob->delete();
        }
    }

    /**
     * @covers \Job::createJobs
     */
    public function testCreateJobs()
    {
        $testJobs = [];
        $seed = rand();
        for ($i = 0; $i < 3; ++$i) {
            $testJob = new Job();
            $testJob->do_number = 'DCPHPv2_'.rand().'_'.$i;
            $testJob->date = date('Y-m-d');
            $testJob->address = 'PHP Islet No.'.$seed;
            array_push($testJobs, $testJob);
        }
        Job::createJobs($testJobs);
        $testJobs = array_map(function ($attrArray) {
            $newJob = new Job($attrArray);
            $newJob = $newJob->hydrate();

            return $newJob;
        }, $testJobs);
        sleep(1);
        $returnedJobs = Job::listJobs(
          [
              'date' => date('Y-m-d'),
              'sort' => 'created_at',
          ],
          'PHP Islet No.'.$seed
        );
        $this->assertEquals(3, count($returnedJobs));
        foreach ($testJobs as $testJob) {
            $found = false;
            foreach ($returnedJobs as $returnedJob) {
                if ($testJob->id == $returnedJob->id) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue(true, $found);
        }
        foreach ($testJobs as $testJob) {
            $testJob->delete();
        }
    }

    public function testCreateJobs2()
    {
        //test with attr construction
        $testJobs = [];
        $testAttrs = [];
        $seed = rand();
        for ($i = 0; $i < 3; ++$i) {
            $testJob = new Job();
            $testJob->do_number = 'DCPHPv2_'.rand().'_'.$i;
            $testJob->date = date('Y-m-d');
            $testJob->address = 'PHP Islet No.'.$seed;
            array_push($testAttrs, json_decode(json_encode($testJob), true));
        }
        $testJobs = Job::createJobs($testAttrs);
        $this->assertInternalType('array', $testJobs);
        $this->assertEquals(3, count($testJobs));
        sleep(1);
        $returnedJobs = Job::listJobs(
          [
              'date' => date('Y-m-d'),
              'sort' => 'created_at',
          ],
          'PHP Islet No.'.$seed
        );
        $this->assertEquals(3, count($returnedJobs));
        foreach ($testJobs as $testJob) {
            $this->assertContains(json_encode($testJob), array_map(function ($returnedJob) {
                return json_encode($returnedJob);
            }, $returnedJobs));
        }
        foreach ($testJobs as $testJob) {
            $testJob->delete();
        }
    }

    /** Tests if we can bulk delete jobs using the Job::deleteJobs function by passing an array of Jobs
     * @covers \Job::deleteJobs
     */
    public function testDeleteJobsViaJobs()
    {
        $testJobs = [];
        $seed = rand();
        for ($i = 0; $i < 3; ++$i) {
            $testJob = new Job();
            $testJob->do_number = 'DCPHPv2_'.$seed.'_'.'testDeleteJobs'.'_'.$i;
            $testJob->date = date('Y-m-d');
            $testJob->address = 'PHP Islet '.$seed;
            array_push($testJobs, $testJob);
        }
        $testJobs = Job::createJobs($testJobs);
        sleep(3);
        $response = Job::deleteJobs($testJobs);
        $this->assertInternalType('array', $response);
        $this->assertEquals(0, count($response));
        $searchResponse = Job::listJobs(
            [
                'date' => date('Y-m-d'),
            ],
          'PHP Islet '.$seed
        );
        $this->assertInternalType('array', $searchResponse);
        $this->assertEquals(0, count($searchResponse));

        $badJob = new Job();
        $badJob->id = 'THISIDDOESNOTEXIST';
        $badJob2 = new Job();
        $badJob2->address = 'Sentinel Island';
        $response = Job::deleteJobs([$badJob, $badJob2]);
        $this->assertInternalType('array', $response);
        $this->assertEquals(2, count($response));
        $this->assertContainsOnlyInstancesOf(Job::class, $response);
        $this->assertEquals($badJob->id, $response[0]->id);
    }

    /** Tests if we can bulk delete jobs using the Job::deleteJobs function by passing an array of attributes
     * @covers \Job::deleteJobs
     */
    public function testDeleteJobsViaArray()
    {
        $testJobs = [];
        $seed = rand();
        for ($i = 0; $i < 3; ++$i) {
            $testJob = [];
            $testJob['do_number'] = 'DCPHPv2_'.$seed.'_'.'testDeleteJobs'.'_'.$i;
            $testJob['date'] = date('Y-m-d');
            $testJob['address'] = 'PHP Islet '.$seed;
            array_push($testJobs, $testJob);
        }
        $testJobs = Job::createJobs($testJobs);
        sleep(3);
        $response = Job::deleteJobs($testJobs);
        $this->assertInternalType('array', $response);
        $this->assertEquals(0, count($response));
        $searchResponse = Job::listJobs(
              [
                  'date' => date('Y-m-d'),
              ],
            'PHP Islet '.$seed
          );
        $this->assertInternalType('array', $searchResponse);
        $this->assertEquals(0, count($searchResponse));
        $badJob = [];
        $badJob['id'] = 'THISIDDOESNOTEXIST';
        $badJob2 = [];
        $badJob2['address'] = 'Sentinel Island';
        $response = Job::deleteJobs([$badJob, $badJob2]);
        $this->assertInternalType('array', $response);
        $this->assertEquals(2, count($response));
        $this->assertContainsOnly('array', $response);
        $this->assertEquals($badJob['id'], $response[0]['id']);
    }
}
