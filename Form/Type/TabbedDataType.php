<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Sidus\EAVModelBundle\Form\Type\DataType;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Translator\TranslatableTrait;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
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
