<?php

namespace Sidus\EAVBootstrapBundle\Action;

use Doctrine\ORM\QueryBuilder;
use Sidus\BaseBundle\Doctrine\RepositoryFinder;
use Sidus\EAVBootstrapBundle\Autocomplete\PagerGeneratorInterface;
use Sidus\EAVBootstrapBundle\Autocomplete\ResponseRendererInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Manager\DataManager;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Show results when searching by a family attribute
 */
class AttributeSearchAction
{
    /** @var PagerGeneratorInterface */
    protected $pagerGenerator;

    /** @var ResponseRendererInterface */
    protected $responseRenderer;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var RepositoryFinder */
    protected $repositoryFinder;

    /** @var DataManager */
    protected $dataManager;

    /**
     * @param PagerGeneratorInterface   $pagerGenerator
     * @param ResponseRendererInterface $responseRenderer
     * @param FamilyRegistry            $familyRegistry
     * @param RepositoryFinder          $repositoryFinder
     * @param DataManager               $dataManager
     */
    public function __construct(
        PagerGeneratorInterface $pagerGenerator,
        ResponseRendererInterface $responseRenderer,
        FamilyRegistry $familyRegistry,
        RepositoryFinder $repositoryFinder,
        DataManager $dataManager
    ) {
        $this->pagerGenerator = $pagerGenerator;
        $this->responseRenderer = $responseRenderer;
        $this->familyRegistry = $familyRegistry;
        $this->repositoryFinder = $repositoryFinder;
        $this->dataManager = $dataManager;
    }

    /**
     * @param Request $request
     * @param string  $attributePath Should looks like FamilyCode.attributeCode
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, $attributePath)
    {
        $attribute = $this->getAttribute($attributePath);
        $qb = $this->getQueryBuilderByAttribute($request, $attribute);
        $pager = $this->pagerGenerator->createPager($qb, $request);

        return $this->responseRenderer->renderResponse($pager, $attribute);
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
            $family = $attribute->getFamily();
            $repository = $this->repositoryFinder->getRepository($family->getDataClass());
            if (!$repository instanceof DataRepository) {
                throw new \UnexpectedValueException(
                    "Repository for class {$family->getDataClass()} must be a DataRepository"
                );
            }
            $eavQb = $repository->createFamilyQueryBuilder($family);
            $attributeQb = $eavQb->attribute($attribute);
            $qb = $eavQb->apply($attributeQb->like($term));
            $qb
                ->select($attributeQb->getColumn())
                ->groupBy($attributeQb->getColumn())
                ->orderBy($attributeQb->getColumn());

            return $qb;
        }

        /** @var array $familyCodes */
        $familyCodes = $attribute->getOption('allowed_families', []);
        $families = [];
        foreach ($familyCodes as $familyCode) {
            $families[] = $this->familyRegistry->getFamily($familyCode);
        }

        return $this->dataManager->getQbForFamiliesAndLabel($families, $term);
    }
}
