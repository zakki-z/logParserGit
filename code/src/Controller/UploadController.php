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
        private LogFileProcessor $fileUploadService,
        private string $logDirectory
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

                return $this->redirectToRoute('app_log_view');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Upload failed: ' . $e->getMessage());
                return $this->redirectToRoute('app_log_view');
            }
        }
        return $this->render('upload/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/process-var-logs', name: 'app_process_var_logs', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function processVarLogs(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('process_var_logs', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_upload');
        }

        try {
            $user = $this->getUser();
            $results = $this->fileUploadService->processVarLogDirectory($user, $this->logDirectory);

            if ($results['success'] > 0) {
                $this->addFlash('success', "Processed {$results['success']} file(s) successfully.");
            }

            if ($results['failed'] > 0) {
                $this->addFlash('warning', "{$results['failed']} file(s) failed to process.");
            }

            if ($results['total'] === 0) {
                $this->addFlash('info', 'No log files found in var/log directory.');
            }

        } catch (\Exception $e) {
            $this->addFlash('error', 'Processing failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_upload');
    }
}
