<?php

namespace Sidus\EAVBootstrapBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode;

/**
 * Adds form_message as a proper form function
 */
class FormExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'form_message',
                null,
                ['node_class' => SearchAndRenderBlockNode::class, 'is_safe' => ['html']]
            ),
        ];
    }
}
