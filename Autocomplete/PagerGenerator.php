<?php

namespace Sidus\EAVBootstrapBundle\Autocomplete;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

/**
 * Generate a pager from a query builder and a request from a Select2 client
 */
class PagerGenerator implements PagerGeneratorInterface
{
    /**
     * @param QueryBuilder $qb
     * @param Request      $request
     *
     * @return Pagerfanta
     */
    public function createPager(QueryBuilder $qb, Request $request)
    {
        // Too bad we can't use the Sidus/FilterBundle DoctrineORMAdapter instead but it's not a dependency
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb, false));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $pager->setMaxPerPage(10);
        try {
            $pager->setCurrentPage($request->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            // Silence exception, fallback to first page
        }

        return $pager;
    }
}
