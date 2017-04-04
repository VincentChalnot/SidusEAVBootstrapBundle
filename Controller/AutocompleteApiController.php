<?php

namespace Sidus\EAVBootstrapBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use PagerFanta\Exception\NotValidCurrentPageException;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used by autocomplete to search the EAV entities
 */
class AutocompleteApiController extends Controller
{
    const FAMILY_SEPARATOR = '|';

    /**
     * @param Request $request
     * @param string  $familyCodes
     *
     * @throws \Sidus\EAVModelBundle\Exception\MissingFamilyException
     * @throws \LogicException
     * @throws \UnexpectedValueException
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request, $familyCodes)
    {
        $familyRegistry = $this->get('sidus_eav_model.family.registry');
        $families = [];
        foreach (explode(self::FAMILY_SEPARATOR, $familyCodes) as $familyCode) {
            $families[$familyCode] = $familyRegistry->getFamily($familyCode);
        }

        $term = rtrim($request->get('term'), '%').'%';

        /** @var DataRepository $repository */
        $repository = $this->getDoctrine()->getRepository($this->getParameter('sidus_eav_model.entity.data.class'));
        $qb = $repository->getQbForFamiliesAndLabel($families, $term);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $pager->setMaxPerPage(10);
        try {
            $pager->setCurrentPage($request->get('page', 1));
        } catch (NotValidCurrentPageException $e) {
            // Silence exception, fallback to first page
        }

        $results = [];
        /** @var DataInterface $data */
        foreach ($pager as $data) {
            $results[] = [
                'id' => $data->getId(),
                'text' => $data->getLabel(),
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
}
