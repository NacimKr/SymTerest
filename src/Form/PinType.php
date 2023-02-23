<?php

namespace App\Form;

use App\Entity\Pin;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;

class PinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // $isEdit = $options["method"] === "PUT";

        // $imageFieldsConstraints = [];

        // if($isEdit){
        //     $imageFieldsConstraints[] =
        //         new Assert\Image([
        //             'maxSize' => "8M",
        //             "maxSizeMessage" => "fichier {{name}} est trop grand",
        //             "mimeTypes" => [
        //                 'image/jpg',
        //                 'image/png',
        //                 'image/jpeg'
        //             ]
        //         ]);
        // }




        $builder
            //->setMethod('GET') -> possibilite de changer la methoode et les action via le setter
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image (JPG or PNG file)',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => "Remove image",
                'download_uri' => false,
                'image_uri' => true,
                'download_label' => false,
                'asset_helper' => false,
                "imagine_pattern" => "thumbnail_web_path",
                "constraints" => new Assert\Image([
                    'maxSize' => "1M",
                    "maxSizeMessage" => "fichier {{name}} est trop grand",
                    "mimeTypes" => [
                        'image/jpg',
                        'image/png',
                        'image/jpeg'
                    ]
                ])
            ])
            ->add('title')
            ->add('description')
            // ->add('createdAt')
            // ->add('updatedAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pin::class,
        ]);
    }
}
