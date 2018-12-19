<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Resource\Job;
use Detrack\DetrackCore\Resource\Vehicle;

/**
 * Tests the coupling between Job and Vehicle.
 */
final class JobVehicleTest extends TestCase
{
    public function testAssign()
    {
        $testJob = new Job();
        $seed = rand();
        $testJob->do_number = 'DCPHPv2_'.$seed;
        $testJob->address = 'PHP Islet';
        $testJob->date = ('Y-m-d');
        $testJob->save();

        $testVehicle = new Vehicle();
        $testVehicle->name = 'PHP Boat';
        $testVehicle->detrack_id = getenv('TEST_DRIVER_ID');
        $testVehicle->save();

        $testJob->assignTo($testVehicle);

        $this->assertEquals($testVehicle->name, $testJob->assign_to);

        $testJob->delete();
        $testVehicle->delete();
    }
}
