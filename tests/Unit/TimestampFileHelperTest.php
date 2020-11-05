<?php

namespace Tests\Unit;

use App\Api\Helpers\TimestampFileHelper;
use Tests\TestCase;

class TimestampFileHelperTest extends TestCase
{
    private const FILENAME = 'test_timestamp_remove_this_file';
    private const ABSTRACT_TIMESTAMP = 100000;
    private string $filePath = '';
    private TimestampFileHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filePath =
            storage_path()
            .DIRECTORY_SEPARATOR
            .TimestampFileHelper::$dirNameForFiles
            .DIRECTORY_SEPARATOR
            .self::FILENAME;
        if (empty($this->filePath))
        {
            throw new \Exception('filePath not set');
        }

        $this->helper = new TimestampFileHelper(self::FILENAME);
    }

    public function testSetTimestampCurrent()
    {
        $this->assertTrue($this->helper->setTimestamp());
    }

    public function testGetTimestampCurrent()
    {
        $this->helper->setTimestamp();
        $timestamp = $this->helper->getTimestamp();
        $this->assertEquals(time(), $timestamp);
    }

    public function testSetTimestampAbstract()
    {
        $this->assertTrue($this->helper->setTimestamp(self::ABSTRACT_TIMESTAMP));
    }

    public function testGetTimestampAbstract()
    {
        $this->helper->setTimestamp(self::ABSTRACT_TIMESTAMP);
        $timestamp = $this->helper->getTimestamp();
        $this->assertEquals(self::ABSTRACT_TIMESTAMP, $timestamp);
    }

    public function testGetTimestampDiff()
    {
        $this->helper->setTimestamp(time()-self::ABSTRACT_TIMESTAMP);
        $diff = $this->helper->getDiffFromLastSetTimestamp(time());
        $this->assertEquals($diff, self::ABSTRACT_TIMESTAMP);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (!unlink($this->filePath))
        {
            throw new \Exception('temporary timestamp file cannot be deleted');
        }
    }


}
