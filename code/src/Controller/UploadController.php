<?php

namespace App\Controller;

use App\Service\FileUpload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UploadController extends AbstractController
{
    public function __construct(
        private FileUpload $fileUploadService
    ) {}

    #[Route('/upload', name: 'app_upload', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('upload/index.html.twig');
    }

    #[Route('/upload', name: 'app_upload_process', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request): Response
    {
        $uploadedFile = $request->files->get('log_file');

        if (!$uploadedFile) {
            $this->addFlash('error', 'Please select a file to upload.');
            return $this->redirectToRoute('app_upload');
        }
        try {
            $user = $this->getUser();

            $result = $this->fileUploadService->upload($uploadedFile, $user);

            $this->addFlash('success', $result['message']);

            return $this->redirectToRoute('app_log_view', [
                'id' => $result['file']->getId()
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Upload failed: ' . $e->getMessage());
            return $this->redirectToRoute('app_upload');
        }
    }
}
