<?php

namespace Sidus\EAVBootstrapBundle\Form\Extension;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Override MopaBootstrapBundle to add the change the error icon class
 *
 * @see \Mopa\Bundle\BootstrapBundle\Form\Type\TabType
 */
class TabFormTypeExtension extends AbstractTypeExtension
{

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return TabType::class;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'error_icon' => 'exclamation-triangle',
            ]
        );
    }
}
