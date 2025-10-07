<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Validation
{
    private const MAX_FILE_SIZE = 10485760;
    private const ALLOWED_EXTENSIONS = ['log', 'txt'];

    public function validate(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new FileException('Invalid file upload');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new FileException('File too large. Maximum size: 10MB');
        }
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new FileException('Invalid file extension. Allowed: .log, .txt');
        }
    }
}
