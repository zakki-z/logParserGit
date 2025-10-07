<?php

namespace App\Service;

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

        $this->entityManager->beginTransaction();

        try {
            $fileInfo = $this->fileInfoFactory->createFromUpload($uploadedFile, $user);

            $storedPath = $this->fileUploadService->upload(
                $uploadedFile,
                $fileInfo->getFileNameTime()
            );

            $this->entityManager->persist($fileInfo);
            $this->entityManager->flush();

            $result = $this->logParserService->parseLogFile($storedPath, $fileInfo);

            foreach ($result['entries'] as $entry) {
                $this->entityManager->persist($entry);
            }
            $this->entityManager->flush();

            $this->entityManager->commit();

            return $result;

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
