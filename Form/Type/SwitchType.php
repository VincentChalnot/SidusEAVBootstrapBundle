<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Displays a nice switch as an alternative to a checkbox
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class SwitchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sidus_switch';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CheckboxType::class;
    }
}
