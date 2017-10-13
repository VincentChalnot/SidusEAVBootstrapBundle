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
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used by autocomplete to search the EAV entities
 */
class AutocompleteApiController
{
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
    public function attributeSearchAction(Request $request, $attributePath)
    {
        $attribute = $this->getAttribute($attributePath);
        $qb = $this->getQueryBuilderByAttribute($request, $attribute);
        $pager = $this->createPager($qb, $request);

        return $this->renderResponse($pager, $attribute);
    }

    /**
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function familySearchAction(Request $request, FamilyInterface $family)
    {
        $qb = $this->getQueryBuilderByFamily($request, $family);
        $pager = $this->createPager($qb, $request);

        return $this->renderResponse($pager);
    }

    /**
     * @param Pagerfanta              $pager
     * @param AttributeInterface|null $attribute
     *
     * @throws \UnexpectedValueException
     *
     * @return JsonResponse
     */
    protected function renderResponse(Pagerfanta $pager, AttributeInterface $attribute = null)
    {
        $results = [];
        /** @var DataInterface $data */
        foreach ($pager as $data) {
            $results[] = $this->parseResult($data, $attribute);
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
     * @param Request            $request
     * @param AttributeInterface $attribute
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilderByAttribute(Request $request, AttributeInterface $attribute)
    {
        $term = '%'.trim($request->get('term'), '%').'%';

        if (!$attribute->getType()->isRelation() && !$attribute->getType()->isEmbedded()) {
            $eavQb = $this->repository->createFamilyQueryBuilder($attribute->getFamily());

            return $eavQb->apply($eavQb->attribute($attribute)->like($term));
        }

        /** @var array $familyCodes */
        $familyCodes = $attribute->getOption('allowed_families', []);
        $families = [];
        foreach ($familyCodes as $familyCode) {
            $families[] = $this->familyRegistry->getFamily($familyCode);
        }

        return $this->repository->getQbForFamiliesAndLabel($families, $term);
    }

    /**
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \UnexpectedValueException
     * @throws \LogicException
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilderByFamily(Request $request, FamilyInterface $family)
    {
        $term = '%'.trim($request->get('term'), '%').'%';

        return $this->repository->getQbForFamiliesAndLabel([$family], $term);
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

    /**
     * @param DataInterface      $data
     * @param AttributeInterface $attribute
     *
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    protected function parseResult(DataInterface $data, AttributeInterface $attribute = null)
    {
        if ($attribute) {
            if ($attribute->getType()->isRelation() || $attribute->getType()->isEmbedded()) {
                // This is not perfect as it does not uses the OptionResolver of the form type to resolve the 'choice_label'
                $label = $this->computeLabelHelper->computeLabel($data, $data->getId(), $attribute->getFormOptions());
            } else {
                $label = $data->get($attribute->getCode());
            }
        } else {
            $label = $data->getLabel();
        }

        return [
            'id' => $data->getId(),
            'text' => $label,
        ];
    }
}
