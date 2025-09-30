<?php

namespace App\Service;

use App\Entity\FileInfo;
use App\Entity\User;
use App\Repository\LogEntriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload
{
    private string $uploadDirectory;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private Validation $ValidationService,
        private LogParser $parsingService,
    )
    {
        $this->uploadDirectory = __DIR__ . '/../../var/uploads/logs';

        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0777, true);
        }
    }
    public function upload(UploadedFile $uploadedFile, User $user): array
    {
        try {
            $this->ValidationService->validate($uploadedFile);


            $fileInfo = new FileInfo();

            $originalName = $uploadedFile->getClientOriginalName();
            $extension = $uploadedFile->getClientOriginalExtension();

            $uniqueId = uniqid('', true);
            $newFilename = $uniqueId . '.' . $extension;

            $fileInfo->setFileName($originalName);
            $fileInfo->setFileNameTime($newFilename);
            $fileInfo->setUploadedAt(new \DateTimeImmutable());
            $fileInfo->setFileSize($uploadedFile->getSize());
            $fileInfo->setUser($user);


            $uploadedFile->move($this->uploadDirectory, $newFilename);

            $this->entityManager->beginTransaction();

            $this->entityManager->persist($fileInfo);
            $this->entityManager->flush();


            $filePath = $this->uploadDirectory . '/' . $newFilename;
            $result = $this->parsingService->parseLogFile($filePath, $fileInfo);

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
