<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Sidus\EAVModelBundle\Form\Type\DataType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Same as DataType but with tabs for attribute groups
 */
class TabbedDataType extends DataType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault(
            'attr',
            [
                'novalidate' => 'novalidate',
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_tabbed_data';
    }
}
