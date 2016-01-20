<?php

namespace Sidus\EAVBootstrapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SidusEAVBootstrapBundle extends Bundle
{
    public function getParent()
    {
        return 'SidusEAVModelBundle';
    }
}
