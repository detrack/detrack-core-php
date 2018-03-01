<?php

require_once "CreateClientTrait.php";

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Client\Exception\InvalidAPIKeyException;
use Detrack\DetrackCore\Model\Delivery;
use Detrack\DetrackCore\Factory\DeliveryFactory;

use Carbon\Carbon;
class DetrackClientTest extends TestCase
{
  protected $client;
  use CreateClientTrait;
  function setUp(){
    $this->createClient();
  }
  public function testBadKeys(){
    $this->expectException(InvalidAPIKeyException::class);
    $badClient = new DetrackClient("This is a bad key!");
    $badClient = new DetrackClient(NULL);
    $badClient = new DetrackClient(new \stdClass());
    $badClient = new DetrackClient(9001);
  }
  /**
  * Tests the bulkSaveDeliveries()'s create functionality in the DeliveryMiscActions traits
  *
  * @covers DeliveryFactory::create();
  * @covers DeliveryMiscActions::bulkSaveDeliveries();
  */
  public function testBulkCreateDeliveries(){
    $newFactory = new DeliveryFactory($this->client);
    $newDeliveries = $newFactory->createFakes(rand(101,201));
    //ensure nothing broke during the bulkSaveDeliveries call
    $this->assertTrue($this->client->bulkSaveDeliveries($newDeliveries));
    //now we try mixing create and update and see how it goes
    $newDeliveries2 = $newFactory->createFakes(rand(101,201));
    foreach($newDeliveries as $newDelivery){
      //modify some fields
      $newDelivery->instructions = "lorem ipsum bottom kek";
    }
    $combinedDeliveries = array_merge_recursive($newDeliveries,$newDeliveries2);
    shuffle($combinedDeliveries);
    //ensure nothing broke during the bulkSaveDeliveries call
    $this->assertTrue($this->client->bulkSaveDeliveries($combinedDeliveries));
    //sample one random delivery from $newDeliveries and $newDeliveries2
    $sampleDelivery = $newDeliveries[rand(0,count($newDeliveries)-1)];
    $sampleDelivery2 = $newDeliveries2[rand(0,count($newDeliveries2)-1)];
    //test if update worked
    $this->assertEquals("lorem ipsum bottom kek",$this->client->findDelivery($sampleDelivery->getIdentifier())->instructions);
    //test if create worked
    $this->assertNotNull($this->client->findDelivery($sampleDelivery2->getIdentifier()));
    return $combinedDeliveries;
  }
  /**
  * Tests the bulkFindDeliveries function to see if we can find the deliveries we just created
  *
  * @depends testBulkCreateDeliveries
  *
  * @covers DeliveryMiscActions::bulkFindDeliveries;
  */
  public function testBulkFindDeliveries($combinedDeliveries){
    //first test finding via delivery objects
    $this->assertEquals(count($combinedDeliveries),count($this->client->bulkFindDeliveries($combinedDeliveries)));
    //then test via delivery identifiers
    $combinedDeliveryIdentifiers = [];
    foreach($combinedDeliveries as $combinedDelivery){
      array_push($combinedDeliveryIdentifiers,$combinedDelivery->getIdentifier());
    }
    $this->assertEquals(count($combinedDeliveries),count($this->client->bulkFindDeliveries($combinedDeliveryIdentifiers)));
    return $combinedDeliveries;
  }
  /**
  * Test the findDeliveriesByDate function to see if we can list all deliveries created today
  *
  * @depends testBulkFindDeliveries
  *
  * @covers DeliveryMiscActions::findDeliveriesByDate
  */
  public function testFindDeliveriesByDate($combinedDeliveries){
    $receivedDeliveries = $this->client->findDeliveriesByDate($combinedDeliveries[0]->date);
    $this->assertInstanceOf(Delivery::class, $receivedDeliveries[rand(0,count($receivedDeliveries))]);
    $this->assertGreaterThanOrEqual(count($combinedDeliveries),count($receivedDeliveries));
    return $combinedDeliveries;
  }
  /**
  * Tests the bulkDeleteDeliveries function to see if we can delete some of the deliveries we created today
  *
  * @depends testFindDeliveriesByDate
  *
  * @covers DeliveryMiscActions::bulkDeleteDeliveries
  */
  public function testBulkDeleteDeliveries($combinedDeliveries){
    //choose a subset of deliveries to deletes
    $toBeDeleted = array_slice($combinedDeliveries,0,rand(1,count($combinedDeliveries)));
    $this->assertSame(true,$this->client->bulkDeleteDeliveries($toBeDeleted));
    $this->assertSame([],$this->client->bulkFindDeliveries($toBeDeleted));
    return array_slice($combinedDeliveries,count($toBeDeleted));
  }
  /**
  * Test the deleteDeliveriesByDate function to see if we can delete all deliveries created today
  *
  * @depends testBulkDeleteDeliveries
  *
  * @covers DeliveryMiscActions::deleteDeliveriesByDate
  */
  public function testDeleteDeliveriesByDate($combinedDeliveries){
    $this->assertSame(true,$this->client->deleteDeliveriesByDate($combinedDeliveries[0]->date));
    $this->assertSame([],$this->client->findDeliveriesByDate($combinedDeliveries[0]->date));
  }
  /**
  * Tests if we can retrieve POD images
  *
  * @covers Delivery::getPODImage
  */
  public function testGetPODImage(){
    try{
      $dotenv = new Dotenv\Dotenv(__DIR__ . "/..");
      $dotenv->load();
    }catch(Exception $ex){
      throw new RuntimeException(".env file not found. Please refer to .env.example and create one.");
    }
    $sampleDO = getenv("SAMPLE_DELIVERY_DO");
    $sampleDate = getenv("SAMPLE_DELIVERY_DATE");
    if($sampleDO == NULL || $sampleDate == NULL){
      $this->markTestSkipped("Sample delivery details not specified in .env. Cannot proceed with testing POD download.");
    }
    $sampleDelivery = $this->client->findDelivery(["date"=>$sampleDate,"do"=>$sampleDO]);
    if($sampleDelivery==NULL){
      $this->markTestSkipped("Cannot find delivery based on details specified in .env. Please double-check with the dashboard.");
    }else{
      $numImages = getenv("SAMPLE_DELIVERY_POD_COUNT");
      $numImages = (int) $numImages;
      if(!is_int($numImages) || $numImages < 0 || $numImages > 5){
        $this->markTestSkipped("Invalid SAMPLE_DELIVERY_POD_COUNT specified in .env. Please double check.");
      }
      for($i=1;$i<=5;$i++){
        $img = $sampleDelivery->getPODImage($i);
        if($i<=$numImages){
          $this->assertInstanceOf(\Intervention\Image\Image::class,$img);
          $this->assertNotNull($img->width());
          $this->assertNotNull($img->height());
          if(getenv("SAMPLE_DELIVERY_POD_SAVE_DIR")!=NULL){
            if(!file_exists(getenv("SAMPLE_DELIVERY_POD_SAVE_DIR").str_replace(" ","",$sampleDelivery->do))){
              mkdir(getenv("SAMPLE_DELIVERY_POD_SAVE_DIR").str_replace(" ","",$sampleDelivery->do),0750,true);
            }
            $img->save(getenv("SAMPLE_DELIVERY_POD_SAVE_DIR").str_replace(" ","",$sampleDelivery->do).DIRECTORY_SEPARATOR.$i.".jpg");
          }
        }else{
          $this->assertNull($img);
        }
      }
    }
  }
  /**
  * Tests if we can retrieve POD PDF file
  *
  * @covers Delivery::getPODPDF
  */
  public function testGetPODPDF(){
    try{
      $dotenv = new Dotenv\Dotenv(__DIR__ . "/..");
      $dotenv->load();
    }catch(Exception $ex){
      throw new RuntimeException(".env file not found. Please refer to .env.example and create one.");
    }
    $sampleDO = getenv("SAMPLE_DELIVERY_DO");
    $sampleDate = getenv("SAMPLE_DELIVERY_DATE");
    if($sampleDO == NULL || $sampleDate == NULL){
      $this->markTestSkipped("Sample delivery details not specified in .env. Cannot proceed with testing POD download.");
    }
    $sampleDelivery = $this->client->findDelivery(["date"=>$sampleDate,"do"=>$sampleDO]);
    if($sampleDelivery==NULL){
      $this->markTestSkipped("Cannot find delivery based on details specified in .env. Please double-check with the dashboard.");
    }else{
      $pdfString = $sampleDelivery->getPODPDF();
      if($pdfString==NULL){
        $this->markTestSkipped("There appears to be no POD PDF available. Please double-check with the dashboard that the delivery has been marked as complete, and that POD images were uploaded.");
      }
      $this->assertNotNull($pdfString);
      $this->assertInternalType("string",$pdfString);
    }
  }
  /**
  * Tests if we can download as save POD PDF file
  *
  * @covers Delivery::downloadPODPDF
  */
  public function testDownloadPODPDF(){
    try{
      $dotenv = new Dotenv\Dotenv(__DIR__ . "/..");
      $dotenv->load();
    }catch(Exception $ex){
      throw new RuntimeException(".env file not found. Please refer to .env.example and create one.");
    }
    $sampleDO = getenv("SAMPLE_DELIVERY_DO");
    $sampleDate = getenv("SAMPLE_DELIVERY_DATE");
    if($sampleDO == NULL || $sampleDate == NULL){
      $this->markTestSkipped("Sample delivery details not specified in .env. Cannot proceed with testing POD download.");
    }
    $sampleDelivery = $this->client->findDelivery(["date"=>$sampleDate,"do"=>$sampleDO]);
    if($sampleDelivery==NULL){
      $this->markTestSkipped("Cannot find delivery based on details specified in .env. Please double-check with the dashboard.");
    }else{
      if(getenv("SAMPLE_DELIVERY_POD_SAVE_DIR")==NULL){
        $this->markTestSkipped("No directory specified in .env for saving sample POD. Please do so to execute this test.");
      }
      $saveDir = getenv("SAMPLE_DELIVERY_POD_SAVE_DIR") . DIRECTORY_SEPARATOR . str_replace(" ","",$sampleDelivery->do);
      $this->assertTrue($sampleDelivery->downloadPODPDF($saveDir . DIRECTORY_SEPARATOR . "POD.pdf"));
    }
  }
}

 ?>
