<?php

namespace App\Service;

use App\Entity\FileInfo;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileInfoFactory
{
    public function createFromUpload(UploadedFile $uploadedFile, User $user): FileInfo
    {
        $fileInfo = new FileInfo();

        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $uniqueFilename = $this->generateUniqueFilename($extension);

        $fileInfo->setFileName($originalName);
        $fileInfo->setFileNameTime($uniqueFilename);
        $fileInfo->setUploadedAt(new \DateTimeImmutable());
        $fileInfo->setFileSize($uploadedFile->getSize());
        $fileInfo->setUser($user);

        return $fileInfo;
    }

    private function generateUniqueFilename(string $extension): string
    {
        return uniqid('', true) . '.' . $extension;
    }
}
