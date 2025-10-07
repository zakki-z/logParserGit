<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class LogFileUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('log_file', FileType::class, [
                'label' => 'Select Log File',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'text/plain',
                            'text/x-log',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid log file (.log or .txt)',
                    ])
                ],
                'attr' => [
                    'accept' => '.log,.txt',
                    'class' => 'form-control w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Upload File',
                'attr' => [
                    'class' => 'btn btn-primary bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200'
                ]
            ]);
    }
}
