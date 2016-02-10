<?php

namespace Sidus\EAVBootstrapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SidusEAVBootstrapBundle extends Bundle
{
    /**
     * Used to override certain attribute types and services from base bundle
     * @return string
     */
    public function getParent()
    {
        return 'SidusEAVModelBundle';
    }
}
