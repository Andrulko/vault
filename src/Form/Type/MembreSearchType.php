<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembreSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, ['required' => false, 'label' => 'name'])
            ->add('prenom', null, ['required' => false, 'label' => 'firstname'])
            ->add('rechercher', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }

    public function getName(): string
    {
        return 're_form_membreSearch';
    }

    public function getBlockPrefix(): string
    {
        return $this->getName();
    }
}
