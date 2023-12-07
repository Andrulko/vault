<?php

namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class GestionnaireParametresType extends GestionnaireType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        // Remove the submit button of user form

        $associationForm = $builder->get('association');
        $associationForm->remove('user');
    }

    public function getBlockPrefix(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return 're_form_gestionnaireParametres';
    }
}
