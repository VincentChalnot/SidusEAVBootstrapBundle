<?php

namespace Sidus\EAVBootstrapBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Sidus\EAVBootstrapBundle\Action\AttributeSearchAction;
use Sidus\EAVBootstrapBundle\Action\FamilySearchAction;
use Sidus\EAVBootstrapBundle\Autocomplete\PagerGeneratorInterface;
use Sidus\EAVBootstrapBundle\Autocomplete\ResponseRendererInterface;
use Sidus\EAVBootstrapBundle\Form\Helper\ComputeLabelHelper;
use Sidus\EAVModelBundle\Doctrine\DataLoaderInterface;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Manager\DataManager;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Registry\FamilyRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated This controller is not supported anymore, please migrate your controller to actions using the
 * autocomplete services helpers
 *
 * Used by autocomplete to search the EAV entities
 */
class AutocompleteApiController
{
    /** @var ComputeLabelHelper */
    protected $computeLabelHelper;

    /** @var FamilyRegistry */
    protected $familyRegistry;

    /** @var DataManager */
    protected $dataManager;

    /** @var DataRepository */
    protected $repository;

    /** @var DataLoaderInterface */
    protected $dataLoader;

    /** @var ResponseRendererInterface */
    protected $responseRenderer;

    /** @var PagerGeneratorInterface */
    protected $pagerGenerator;

    /** @var AttributeSearchAction */
    protected $attributeSearchAction;

    /** @var FamilySearchAction */
    protected $familySearchAction;

    /**
     * @deprecated kept for back compatibility only!
     *
     * @param ComputeLabelHelper  $computeLabelHelper
     * @param FamilyRegistry      $familyRegistry
     * @param DataManager         $dataManager
     * @param ManagerRegistry     $doctrine
     * @param string              $dataClass
     * @param DataLoaderInterface $dataLoader
     */
    public function __construct(
        ComputeLabelHelper $computeLabelHelper,
        FamilyRegistry $familyRegistry,
        DataManager $dataManager,
        ManagerRegistry $doctrine,
        $dataClass,
        DataLoaderInterface $dataLoader
    ) {
        $m = __CLASS__.' is deprecated, consider migrating to the AttributeSearchAction / FamilySearchAction actions';
        @trigger_error($m, E_USER_DEPRECATED);

        $this->computeLabelHelper = $computeLabelHelper;
        $this->familyRegistry = $familyRegistry;
        $this->dataManager = $dataManager;
        $this->repository = $doctrine->getRepository($dataClass);
        $this->dataLoader = $dataLoader;
    }

    /**
     * @param ResponseRendererInterface $responseRenderer
     */
    public function setResponseRenderer(ResponseRendererInterface $responseRenderer)
    {
        $this->responseRenderer = $responseRenderer;
    }

    /**
     * @param PagerGeneratorInterface $pagerGenerator
     */
    public function setPagerGenerator(PagerGeneratorInterface $pagerGenerator)
    {
        $this->pagerGenerator = $pagerGenerator;
    }

    /**
     * @param AttributeSearchAction $attributeSearchAction
     */
    public function setAttributeSearchAction(AttributeSearchAction $attributeSearchAction)
    {
        $this->attributeSearchAction = $attributeSearchAction;
    }

    /**
     * @param FamilySearchAction $familySearchAction
     */
    public function setFamilySearchAction(FamilySearchAction $familySearchAction)
    {
        $this->familySearchAction = $familySearchAction;
    }

    /**
     * @deprecated use the AttributeSearchAction action/service instead
     *
     * @param Request $request
     * @param string  $attributePath Should looks like FamilyCode.attributeCode
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function attributeSearchAction(Request $request, $attributePath)
    {
        $m = __METHOD__.' is deprecated, consider using the '.AttributeSearchAction::class.' action/service instead';
        @trigger_error($m, E_USER_DEPRECATED);

        return $this->attributeSearchAction->__invoke($request, $attributePath);
    }

    /**
     * @deprecated use the FamilySearchAction action/service instead
     *
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function familySearchAction(Request $request, FamilyInterface $family)
    {
        $m = __METHOD__.' is deprecated, consider using the '.FamilySearchAction::class.' action/service instead';
        @trigger_error($m, E_USER_DEPRECATED);

        return $this->familySearchAction->__invoke($request, $family);
    }

    /**
     * @deprecated Use the ResponseRenderer service instead
     *
     * @param Pagerfanta              $pager
     * @param AttributeInterface|null $attribute
     *
     * @throws \UnexpectedValueException
     *
     * @return JsonResponse
     */
    protected function renderResponse(Pagerfanta $pager, AttributeInterface $attribute = null)
    {
        $m = __METHOD__.' is deprecated, consider using the '.ResponseRendererInterface::class.' instead';
        @trigger_error($m, E_USER_DEPRECATED);

        return $this->responseRenderer->renderResponse($pager, $attribute);
    }

    /**
     * @deprecated This method is now internal to the AttributeSearchAction action/service
     *
     * @param string $attributePath
     *
     * @throws \BadMethodCallException
     */
    protected function getAttribute($attributePath)
    {
        $m = __METHOD__.' is deprecated and now internal to the '.AttributeSearchAction::class.' action/service';

        throw new \BadMethodCallException($m);
    }

    /**
     * @deprecated This method is now internal to the AttributeSearchAction action/service
     *
     * @param Request            $request
     * @param AttributeInterface $attribute
     *
     * @throws \BadMethodCallException
     */
    protected function getQueryBuilderByAttribute(Request $request, AttributeInterface $attribute)
    {
        $m = __METHOD__.' is deprecated and now internal to the '.AttributeSearchAction::class.' action/service';

        throw new \BadMethodCallException($m);
    }

    /**
     * @deprecated This method is now internal to the FamilySearchAction action/service
     *
     * @param Request         $request
     * @param FamilyInterface $family
     *
     * @throws \BadMethodCallException
     */
    protected function getQueryBuilderByFamily(Request $request, FamilyInterface $family)
    {
        $m = __METHOD__.' is deprecated and now internal to the '.FamilySearchAction::class.' action/service';

        throw new \BadMethodCallException($m);
    }

    /**
     * @param QueryBuilder $qb
     * @param Request      $request
     *
     * @return Pagerfanta
     */
    protected function createPager(QueryBuilder $qb, Request $request)
    {
        $m = __METHOD__.' is deprecated, consider using the '.PagerGeneratorInterface::class.' instead';
        @trigger_error($m, E_USER_DEPRECATED);

        return $this->pagerGenerator->createPager($qb, $request);
    }

    /**
     * @deprecated This method is now internal to the ReponseRenderer service
     *
     * @param DataInterface      $data
     * @param AttributeInterface $attribute
     *
     * @throws \BadMethodCallException
     */
    protected function parseResult(DataInterface $data, AttributeInterface $attribute = null)
    {
        $m = __METHOD__.' is deprecated, use the '.ResponseRendererInterface::class.' service instead';

        throw new \BadMethodCallException($m);
    }
}
