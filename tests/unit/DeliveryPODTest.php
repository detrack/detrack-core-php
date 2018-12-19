<?php

use PHPUnit\Framework\TestCase;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Extra tests for testing POD features. You must fill up the relevant .env fields for these to work.
 */
class DeliveryPODTest extends TestCase
{
    protected $client;
    use CreateClientTrait;

    public function setUp()
    {
        $this->createClient();
        try {
            $dotenv = new Dotenv\Dotenv(__DIR__.'/../..');
            $dotenv->load();
        } catch (Exception $ex) {
            throw new RuntimeException('.env file not found. Please refer to .env.example and create one.');
        }
        $sampleDO = getenv('SAMPLE_DELIVERY_DO');
        $sampleDate = getenv('SAMPLE_DELIVERY_DATE');
        if ($sampleDO == null || $sampleDate == null) {
            $this->markTestSkipped('Sample delivery details not specified in .env. Cannot proceed with testing POD download.');
        }
        $sampleDelivery = $this->client->findDelivery(['date' => $sampleDate, 'do' => $sampleDO]);
        $this->sampleDelivery = $sampleDelivery;
        if ($sampleDelivery == null) {
            $this->markTestSkipped('Cannot find delivery based on details specified in .env. Please double-check with the dashboard.');
        }
    }

    /**
     * Tests if we can retrieve POD images.
     *
     * @covers \Delivery::getPODImage
     */
    public function testGetPODImage()
    {
        $sampleDelivery = $this->sampleDelivery;
        $numImages = getenv('SAMPLE_DELIVERY_POD_COUNT');
        $numImages = (int) $numImages;
        if (!is_int($numImages) || $numImages < 0 || $numImages > 5) {
            $this->markTestSkipped('Invalid SAMPLE_DELIVERY_POD_COUNT specified in .env. Please double check.');
        }
        for ($i = 1; $i <= 5; ++$i) {
            $img = $sampleDelivery->getPODImage($i);
            if ($i <= $numImages) {
                $img = Image::make($img);
                $this->assertNotNull($img->width());
                $this->assertNotNull($img->height());
            } else {
                $this->assertNull($img);
            }
        }
    }

    /**
     * Tests if we can download POD images and write to disk.
     *
     * @covers \Delivery::downloadPODPDF
     */
    public function testDownloadPODImage()
    {
        $sampleDelivery = $this->sampleDelivery;
        if (getenv('SAMPLE_DELIVERY_POD_SAVE_DIR') == null) {
            $this->markTestSkipped('No directory specified in .env for saving sample POD. Please do so to execute this test.');
        } else {
            $numImages = getenv('SAMPLE_DELIVERY_POD_COUNT');
            $numImages = (int) $numImages;
            if (!is_int($numImages) || $numImages < 0 || $numImages > 5) {
                $this->markTestSkipped('Invalid SAMPLE_DELIVERY_POD_COUNT specified in .env. Please double check.');
            }
            if (!is_writable(getenv('SAMPLE_DELIVERY_POD_SAVE_DIR'))) {
                $this->markTestSkipped('Sample Delivery POD Save Directory not writable. Skipping Test.');
            }
            $folder = getenv('SAMPLE_DELIVERY_POD_SAVE_DIR').preg_replace("/[^\w\d]/", '', $sampleDelivery->do).'-'.time();
            for ($i = 1; $i <= 5; ++$i) {
                $path = $folder.DIRECTORY_SEPARATOR.$i.'.jpg';
                if ($i <= $numImages) {
                    $this->assertInternalType('int', $sampleDelivery->downloadPODImage($i, $path));
                    $this->assertTrue(file_exists($path));
                    $img = Image::make($path);
                    $this->assertNotNull($img);
                    $this->assertNotNull($img->width());
                    $this->assertNotNull($img->height());
                    unlink($path);
                } else {
                    $this->expectException(\RuntimeException::class);
                    $this->assertTrue($sampleDelivery->downloadPODImage($i, $path));
                }
            }
            rmdir($folder);
        }
    }

    /**
     * Tests if we can retrieve POD PDF file.
     *
     * @covers \Delivery::getPODPDF
     */
    public function testGetPODPDF()
    {
        $sampleDelivery = $this->sampleDelivery;
        $pdfString = $sampleDelivery->getPODPDF();
        if ($pdfString == null) {
            $this->markTestSkipped('There appears to be no POD PDF available. Please double-check with the dashboard that the delivery has been marked as complete, and that POD images were uploaded.');
        }
        $this->assertNotNull($pdfString);
        $this->assertInternalType('string', $pdfString);
    }

    /**
     * Tests if we can download as save POD PDF file.
     *
     * @covers \Delivery::downloadPODPDF
     */
    public function testDownloadPODPDF()
    {
        $sampleDelivery = $this->sampleDelivery;
        if (getenv('SAMPLE_DELIVERY_POD_SAVE_DIR') == null) {
            $this->markTestSkipped('No directory specified in .env for saving sample POD. Please do so to execute this test.');
        }
        if (!is_writable(getenv('SAMPLE_DELIVERY_POD_SAVE_DIR'))) {
            $this->markTestSkipped('Sample Delivery POD Save Directory not writable. Skipping Test.');
        }
        $folder = getenv('SAMPLE_DELIVERY_POD_SAVE_DIR').preg_replace("/[^\w\d]/", '', $sampleDelivery->do).'-'.time();
        $this->assertInternalType('int', $sampleDelivery->downloadPODPDF($folder.DIRECTORY_SEPARATOR.'POD.pdf'));
        unlink($folder.DIRECTORY_SEPARATOR.'POD.pdf');
        rmdir($folder);
    }
}
