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
    private string $uploadPath;

    public function __construct(
        string $uploadDirectory,
        string $uploadPath
    )
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->uploadPath = $uploadPath;
        $this->ensureDirectoryExists($this->uploadDirectory);
        $this->ensureDirectoryExists($this->uploadPath);
    }
    public function upload(UploadedFile $file, string $filename): string
    {
        $file->move($this->uploadDirectory, $filename);
        $file->move($this->uploadPath, $filename);
        $sourcePath = $this->uploadDirectory . '/' . $filename;
        $targetPath = $this->uploadPath . '/' . $filename;
        copy($sourcePath, $targetPath);
        return $sourcePath;
    }
    private function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
