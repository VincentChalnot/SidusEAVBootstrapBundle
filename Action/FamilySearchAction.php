<?php

namespace Sidus\EAVBootstrapBundle\Action;

use Doctrine\ORM\QueryBuilder;
use Sidus\EAVBootstrapBundle\Autocomplete\PagerGeneratorInterface;
use Sidus\EAVBootstrapBundle\Autocomplete\ResponseRendererInterface;
use Sidus\EAVModelBundle\Manager\DataManager;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Search EAV data by families
 */
class FamilySearchAction
{
    /** @var PagerGeneratorInterface */
    protected $pagerGenerator;

    /** @var ResponseRendererInterface */
    protected $responseRenderer;

    /** @var DataManager */
    protected $dataManager;

    /**
     * @param PagerGeneratorInterface   $pagerGenerator
     * @param ResponseRendererInterface $responseRenderer
     * @param DataManager               $dataManager
     */
    public function __construct(
        PagerGeneratorInterface $pagerGenerator,
        ResponseRendererInterface $responseRenderer,
        DataManager $dataManager
    ) {
        $this->pagerGenerator = $pagerGenerator;
        $this->responseRenderer = $responseRenderer;
        $this->dataManager = $dataManager;
    }

    /**
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, FamilyInterface $family)
    {
        $qb = $this->getQueryBuilderByFamily($request, $family);
        $pager = $this->pagerGenerator->createPager($qb, $request);

        return $this->responseRenderer->renderResponse($pager);
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

        return $this->dataManager->getQbForFamiliesAndLabel([$family], $term);
    }
}
