<?php

namespace App\Form\Type;

use App\Entity\Adresse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdresseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'street',
                'label_attr' => ['class' => 'font-size-1'],
            ])
            ->add('ville', null, [
                'label' => 'city',
                'label_attr' => ['class' => 'font-size-1'],
            ])
            ->add('codePostal', null, [
                'label' => 'postal_code',
                'label_attr' => ['class' => 'font-size-1'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresse::class,
            'validation_groups' => ['adresse'],
        ]);
    }

    public function getName(): string
    {
        return 're_form_adresse';
    }
}
