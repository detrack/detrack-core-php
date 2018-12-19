<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClientStatic;
use Detrack\DetrackCore\Resource\Vehicle;

final class VehicleTest extends TestCase
{
    public function testSaveVehicle()
    {
        $newVehicle = new Vehicle();
        $newVehicle->name = 'PHP Boat';
        $newVehicle->detrack_id = getenv('TEST_DRIVER_ID');
        $newVehicle->mobile_number = '99999999';
        $newVehicle->save();
        //var_dump($newVehicle);
        $response = DetrackClientStatic::sendData('GET', 'vehicles/'.$newVehicle->id, null);
        $this->assertEquals($newVehicle->mobile_number, $response->data->mobile_number);
        $this->assertEquals($newVehicle->name, $response->data->name);
        $this->assertEquals($newVehicle->detrack_id, $response->data->detrack_id);
        $this->assertNotNull($newVehicle->id);

        $newVehicle->mobile_number = '91234567';
        $newVehicle->save();
        $response = DetrackClientStatic::sendData('GET', 'vehicles/'.$newVehicle->id, null);
        $this->assertEquals($newVehicle->mobile_number, $response->data->mobile_number);

        return $newVehicle->id;
    }

    /**
     * @depends testSaveVehicle
     */
    public function testHydrateVehicle(string $newVehicleId)
    {
        $newVehicle = new Vehicle();
        $newVehicle->id = $newVehicleId;
        $newVehicle->hydrate();
        $this->assertEquals($newVehicle->mobile_number, '91234567');
        $this->assertEquals($newVehicle->name, 'PHP Boat');
        $this->assertEquals($newVehicle->detrack_id, getenv('TEST_DRIVER_ID'));

        return $newVehicle->id;
    }

    /**
     * @depends testHydrateVehicle
     */
    public function testDeleteVehicle(string $newVehicleId)
    {
        $newVehicle = new Vehicle();
        $newVehicle->id = $newVehicleId;
        $this->assertTrue($newVehicle->delete());
        $response = DetrackClientStatic::sendData('GET', 'vehicles/'.$newVehicleId, null);
        $this->assertEquals('not_found', $response->code);
    }
}
