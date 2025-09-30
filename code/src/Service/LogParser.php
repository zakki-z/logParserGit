<?php

namespace App\Service;

use App\Entity\FileInfo;
use App\Entity\LogEntries;

class LogParser
{
    private const PATTERN = '/^\[([^\]]+)\]\s+([^.]+)\.([A-Z]+):\s+(.+)$/';
    public function parseLogFile(string $filePath, FileInfo $file): array
    {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);

        $parsedEntries = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;


            if (preg_match(self::PATTERN, $line, $matches)) {
                $logEntry = new LogEntries();

                try {
                    $date = new \DateTime($matches[1]);
                    $logEntry->setDate($date);
                } catch (\Exception $e) {
                    continue;
                }

                $logEntry->setChannel($matches[2]);
                $logEntry->setType($matches[3]);
                $logEntry->setInformation($matches[4]);
                $logEntry->setFile($file);

                $parsedEntries[] = $logEntry;
            }
        }

        return [
            'status' => 'success',
            'message' => count($parsedEntries) . ' entries parsed successfully',
            'entries' => $parsedEntries,
            'file' => $file
        ];
    }
}
