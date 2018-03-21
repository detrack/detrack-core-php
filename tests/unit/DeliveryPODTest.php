<?php

use PHPUnit\Framework\TestCase;
use Detrack\DetrackCore\Client\DetrackClient;
use Detrack\DetrackCore\Model\Delivery;
use Intervention\Image\ImageManagerStatic as Image;

/**
* Extra tests for testing POD features. You must fill up the relevant .env fields for these to work.
*/
class DeliveryPODTest extends TestCase{
  protected $client;
  use CreateClientTrait;
  function setUp(){
    $this->createClient();
    try{
      $dotenv = new Dotenv\Dotenv(__DIR__ . "/../..");
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
    $this->sampleDelivery = $sampleDelivery;
    if($sampleDelivery==NULL){
      $this->markTestSkipped("Cannot find delivery based on details specified in .env. Please double-check with the dashboard.");
    }
  }

  /**
  * Tests if we can retrieve POD images
  *
  * @covers Delivery::getPODImage
  */
  public function testGetPODImage(){
    $sampleDelivery = $this->sampleDelivery;
    $numImages = getenv("SAMPLE_DELIVERY_POD_COUNT");
    $numImages = (int) $numImages;
    if(!is_int($numImages) || $numImages < 0 || $numImages > 5){
      $this->markTestSkipped("Invalid SAMPLE_DELIVERY_POD_COUNT specified in .env. Please double check.");
    }
    for($i=1;$i<=5;$i++){
      $img = $sampleDelivery->getPODImage($i);
      if($i<=$numImages){
        $img = Image::make($img);
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
  /**
  * Tests if we can download POD images and write to disk
  *
  * @covers Delivery::downloadPODPDF
  */
  public function testDownloadPODImage(){
    $sampleDelivery = $this->sampleDelivery;
    if(getenv("SAMPLE_DELIVERY_POD_SAVE_DIR")==NULL){
      $this->markTestSkipped("No directory specified in .env for saving sample POD. Please do so to execute this test.");
    }else{
      $numImages = getenv("SAMPLE_DELIVERY_POD_COUNT");
      $numImages = (int) $numImages;
      if(!is_int($numImages) || $numImages < 0 || $numImages > 5){
        $this->markTestSkipped("Invalid SAMPLE_DELIVERY_POD_COUNT specified in .env. Please double check.");
      }
      for($i=1;$i<=5;$i++){
        $path = getenv("SAMPLE_DELIVERY_POD_SAVE_DIR").str_replace(" ","",$sampleDelivery->do).DIRECTORY_SEPARATOR.$i.".jpg";
        if($i<=$numImages){
          $this->assertTrue($sampleDelivery->downloadPODImage($i,$path));
          $this->assertTrue(file_exists($path));
          $img = Image::make($path);
          $this->assertNotNull($img);
          $this->assertNotNull($img->width());
          $this->assertNotNull($img->height());
        }else{
          $this->expectException(\RuntimeException::class);
          $this->assertTrue($sampleDelivery->downloadPODImage($i,$path));
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
    $sampleDelivery = $this->sampleDelivery;
    $pdfString = $sampleDelivery->getPODPDF();
    if($pdfString==NULL){
      $this->markTestSkipped("There appears to be no POD PDF available. Please double-check with the dashboard that the delivery has been marked as complete, and that POD images were uploaded.");
    }
    $this->assertNotNull($pdfString);
    $this->assertInternalType("string",$pdfString);
  }
  /**
  * Tests if we can download as save POD PDF file
  *
  * @covers Delivery::downloadPODPDF
  */
  public function testDownloadPODPDF(){
    $sampleDelivery = $this->sampleDelivery;
    if(getenv("SAMPLE_DELIVERY_POD_SAVE_DIR")==NULL){
      $this->markTestSkipped("No directory specified in .env for saving sample POD. Please do so to execute this test.");
    }
    $saveDir = getenv("SAMPLE_DELIVERY_POD_SAVE_DIR") . DIRECTORY_SEPARATOR . str_replace(" ","",$sampleDelivery->do);
    $this->assertTrue($sampleDelivery->downloadPODPDF($saveDir . DIRECTORY_SEPARATOR . "POD.pdf"));
  }
}

 ?>
