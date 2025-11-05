<?php

namespace App\Service;

use App\Entity\FileInfo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LogFileProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Validation $validationService,
        private FileUpload $fileUploadService,
        private FileInfoFactory $fileInfoFactory,
        private LogParser $logParserService
    ) {}

    public function process(UploadedFile $uploadedFile, User $user): array
    {
        $this->validationService->validate($uploadedFile);

        $fileInfo = $this->fileInfoFactory->createFromUpload($uploadedFile, $user);

        $storedPath = $this->fileUploadService->upload(
            $uploadedFile,
            $fileInfo->getFileNameTime()
        );

        return $this->processLogFile($storedPath, $fileInfo);
    }

    public function processExistingFile(string $filePath, User $user): array
    {
        $fileInfo = new FileInfo();
        $fileInfo->setFileName(basename($filePath));
        $fileInfo->setFileNameTime(basename($filePath));
        $fileInfo->setUploadedAt(new \DateTimeImmutable());
        $fileInfo->setFileSize(filesize($filePath));
        $fileInfo->setUser($user);

        return $this->processLogFile($filePath, $fileInfo);
    }

    public function processVarLogDirectory(User $user, string $logDirectory): array
    {
        $files = glob($logDirectory . '/*.{log,txt}', GLOB_BRACE);

        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($files),
            'details' => []
        ];

        foreach ($files as $file) {
            try {
                $result = $this->processExistingFile($file, $user);
                $results['success']++;
                $results['details'][] = [
                    'file' => basename($file),
                    'status' => 'success',
                    'message' => $result['message']
                ];
            } catch (\Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'file' => basename($file),
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    private function processLogFile(string $filePath, FileInfo $fileInfo): array
    {
        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($fileInfo);
            $this->entityManager->flush();

            $result = $this->logParserService->parseLogFile($filePath, $fileInfo);

            foreach ($result['entries'] as $entry) {
                $this->entityManager->persist($entry);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $result;

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
