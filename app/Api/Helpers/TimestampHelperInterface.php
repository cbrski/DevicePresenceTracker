<?php declare(strict_types=1);

namespace App\Api\Helpers;

interface TimestampHelperInterface
{
    public function __construct(string $_fileName, string $_filePath = null);
    public function setTimestamp(int $timestamp = null): bool;
    public function getTimestamp();
    public function getDiffFromLastSetTimestamp(int $timestampToDiff = null);
    public function getConfig(): array;
}
