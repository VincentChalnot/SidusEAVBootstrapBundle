<?php

namespace Sidus\EAVBootstrapBundle\Autocomplete;

use Pagerfanta\Pagerfanta;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Render a list of data ready to be used by Select2
 */
interface ResponseRendererInterface
{
    /**
     * @param Pagerfanta              $pager
     * @param AttributeInterface|null $attribute
     *
     * @return JsonResponse
     */
    public function renderResponse(Pagerfanta $pager, AttributeInterface $attribute = null);
}
