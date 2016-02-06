<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;


use Symfony\Component\Form\AbstractType;

class SwitchType extends AbstractType
{
    public function getName()
    {
        return 'sidus_switch';
    }

    public function getParent()
    {
        return 'checkbox';
    }
}