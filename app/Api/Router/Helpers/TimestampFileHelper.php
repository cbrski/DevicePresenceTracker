<?php declare(strict_types=1);

namespace App\Api\Helpers;


class TimestampFileHelper implements TimestampHelperInterface
{
    private string $fileName;
    private string $dirPath;
    private string $filePath;
    private bool $timestampSet = false;
    public static string $dirNameForFiles = 'timestamp_files';

    private function checkDirPath() {
        if (!file_exists($this->dirPath))
        {
            mkdir($this->dirPath);
        }
    }

    private function checkFilePath() {
        if (file_exists($this->filePath))
        {
            $this->timestampSet = true;
        }
    }

    public function isTimestampSet(): bool
    {
        return $this->timestampSet;
    }

    public function __construct(string $_fileName, string $_filePath = null)
    {
        $this->fileName = $_fileName;
        $this->dirPath = $_filePath ?? storage_path().DIRECTORY_SEPARATOR.self::$dirNameForFiles;
        $this->filePath = $this->dirPath.DIRECTORY_SEPARATOR.$this->fileName;

        $this->checkDirPath();
        $this->checkFilePath();
    }

    public function setTimestamp(int $timestamp = null): bool
    {
        $timestamp = $timestamp ?? time();
        if (touch($this->dirPath.DIRECTORY_SEPARATOR.$this->fileName, $timestamp))
        {
            $this->timestampSet = true;
            return true;
        }
        return false;
    }

    public function getTimestamp()
    {
        if ($this->isTimestampSet() && $stat = stat($this->filePath))
        {
            return $stat['mtime'];
        }
        return false;
    }

    public function getDiffFromLastSetTimestamp(int $timestampToDiff = null)
    {
        $timestampToDiff = $timestampToDiff ?? time();
        if ($this->isTimestampSet() && $timestamp = $this->getTimestamp())
        {
            return $timestampToDiff - $timestamp;
        }
        return false;
    }

    public function getConfig(): array
    {
        return [
            'fileName' => $this->fileName,
            'dirPath' => $this->dirPath,
            'filePath' => $this->filePath,
            'isTimestampSet' => $this->isTimestampSet(),
        ];
    }
}
