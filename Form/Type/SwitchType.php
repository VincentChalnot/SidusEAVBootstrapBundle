<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;


use Symfony\Component\Form\AbstractType;

class SwitchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sidus_switch';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'checkbox';
    }
}
