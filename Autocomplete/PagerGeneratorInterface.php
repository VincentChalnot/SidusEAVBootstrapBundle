<?php

namespace Sidus\EAVBootstrapBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generate a pager from a query builder and a request from a Select2 client
 */
interface PagerGeneratorInterface
{
    /**
     * @param QueryBuilder $qb
     * @param Request      $request
     *
     * @return Pagerfanta
     */
    public function createPager(QueryBuilder $qb, Request $request);
}
