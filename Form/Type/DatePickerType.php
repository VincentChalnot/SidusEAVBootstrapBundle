<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Enabled a simple bootstrap datepicker on date widgets
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DatePickerType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'widget' => 'single_text',
                'datepicker' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DateType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_date_picker';
    }
}
