<?php

namespace App\Controller;

use App\Form\LogFileUploadType;
use App\Service\LogFileProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UploadController extends AbstractController
{
    public function __construct(
        private LogFileProcessor $fileUploadService
    ) {}
    #[Route('/upload', name: 'app_upload', methods: ['POST','GET'])]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request): Response
    {
        $form = $this->createForm(LogFileUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('log_file')->getData();
            try {
                $user = $this->getUser();

                $result = $this->fileUploadService->process($uploadedFile, $user);

                $this->addFlash('success', $result['message']);

                return $this->redirectToRoute('app_log_view', [
                    'id' => $result['file']->getId()
                ]);

            } catch (\Exception $e) {
                $this->addFlash('error', 'Upload failed: ' . $e->getMessage());
                return $this->redirectToRoute('app_upload');
            }
        }
        return $this->render('upload/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
