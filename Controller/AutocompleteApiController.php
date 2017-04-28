<?php

namespace Sidus\EAVBootstrapBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use PagerFanta\Exception\NotValidCurrentPageException;
use Sidus\EAVBootstrapBundle\Form\Helper\ComputeLabelHelper;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used by autocomplete to search the EAV entities
 */
class AutocompleteApiController
{
    const FAMILY_SEPARATOR = '|';

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var ComputeLabelHelper */
    protected $computeLabelHelper;

    /** @var DataRepository */
    protected $repository;

    /**
     * @param ComputeLabelHelper $computeLabelHelper
     * @param FamilyRegistry     $familyRegistry
     * @param Registry           $doctrine
     * @param string             $dataClass
     */
    public function __construct(
        ComputeLabelHelper $computeLabelHelper,
        FamilyRegistry $familyRegistry,
        Registry $doctrine,
        $dataClass
    ) {
        $this->computeLabelHelper = $computeLabelHelper;
        $this->familyRegistry = $familyRegistry;
        $this->repository = $doctrine->getRepository($dataClass);
    }

    /**
     * @param Request $request
     * @param string  $attributePath Should looks like FamilyCode.attributeCode
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request, $attributePath)
    {
        $attribute = $this->getAttribute($attributePath);
        $qb = $this->getQueryBuilder($attribute, $request);
        $pager = $this->createPager($qb, $request);

        $results = [];
        /** @var DataInterface $data */
        foreach ($pager as $data) {
            // This is not perfect as it does not uses the OptionResolver of the form type to resolve the 'choice_label'
            $label = $this->computeLabelHelper->computeLabel($data, $data->getId(), $attribute->getFormOptions());
            $results[] = [
                'id' => $data->getId(),
                'text' => $label,
            ];
        }

        $headers = [
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'private',
            'Expires' => 0,
        ];

        $response = [
            'results' => $results,
            'pagination' => [
                'more' => $pager->hasNextPage(),
            ],
        ];

        return new JsonResponse($response, 200, $headers);
    }

    /**
     * @param string $attributePath
     *
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     * @throws \Sidus\EAVModelBundle\Exception\MissingAttributeException
     *
     * @return AttributeInterface
     */
    protected function getAttribute($attributePath)
    {
        list($familyCode, $attributeCode) = explode('.', $attributePath);
        $family = $this->familyRegistry->getFamily($familyCode);

        return $family->getAttribute($attributeCode);
    }

    /**
     * @param AttributeInterface $attribute
     * @param Request            $request
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(AttributeInterface $attribute, Request $request)
    {
        $term = '%'.trim($request->get('term'), '%').'%';

        /** @var array $familyCodes */
        $familyCodes = $attribute->getOption('allowed_families', []);
        $families = [];
        foreach ($familyCodes as $familyCode) {
            $families[] = $this->familyRegistry->getFamily($familyCode);
        }

        return $this->repository->getQbForFamiliesAndLabel($families, $term);
    }

    /**
     * @param QueryBuilder $qb
     * @param Request      $request
     *
     * @return Pagerfanta
     */
    protected function createPager(QueryBuilder $qb, Request $request)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
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
