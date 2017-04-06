<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Enabled a simple bootstrap datepicker on datetime widgets
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DateTimePickerType extends AbstractType
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
                'datetimepicker' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DateTimeType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_datetime_picker';
    }
}
