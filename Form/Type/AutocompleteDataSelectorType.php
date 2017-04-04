<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Sidus\EAVBootstrapBundle\Controller\AutocompleteApiController;
use Sidus\EAVBootstrapBundle\Form\ChoiceList\EmptyChoiceLoader;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Form\Type\SimpleDataSelectorType;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use UnexpectedValueException;
use Symfony\Component\Routing\Exception\ExceptionInterface;

/**
 * Class AutocompleteDataSelectorType
 *
 * @package Sidus\EAVBootstrapBundle\Form\Type
 */
class AutocompleteDataSelectorType extends AbstractType
{
    /** @var RouterInterface */
    protected $router;

    /** @var DataRepository */
    protected $repository;

    /**
     * @param RouterInterface $router
     * @param Registry        $doctrine
     * @param string          $dataClass
     */
    public function __construct(RouterInterface $router, Registry $doctrine, $dataClass)
    {
        $this->router = $router;
        $this->repository = $doctrine->getRepository($dataClass);
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     *
     * @throws \RuntimeException
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getData();
        if ($data) { // Set the current data in the choices
            $value = $form->getViewData();
            $view->vars['choices'] = [
                $value => new ChoiceView($data, $value, (string) $data),
            ];
        }

        if ($options['auto_init']) {
            if (empty($view->vars['attr']['class'])) {
                $view->vars['attr']['class'] = '';
            } else {
                $view->vars['attr']['class'] .= ' ';
            }
            $view->vars['attr']['class'] .= 'select2';
            if (!$options['required']) {
                $view->vars['attr']['data-allow-clear'] = 'true';
                $view->vars['attr']['data-placeholder'] = '';
            }
        }

        /** @var FamilyInterface[] $allowedFamilies */
        $allowedFamilies = $options['allowed_families'];
        $familyCodes = [];
        foreach ($allowedFamilies as $allowedFamily) {
            $familyCodes[] = $allowedFamily->getCode();
        }
        try {
            $view->vars['attr']['data-query-uri'] = $this->router->generate(
                'sidus_autocomplete_api_search',
                [
                    'familyCodes' => implode(AutocompleteApiController::FAMILY_SEPARATOR, $familyCodes),
                ]
            );
        } catch (ExceptionInterface $e) {
            throw new \RuntimeException('Unable to generate autocomplete route', 0, $e);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws TransformationFailedException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->resetModelTransformers();
        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($originalData) {
                    return $originalData;
                },
                function ($submittedData) use ($options) {
                    if (null === $submittedData || '' === $submittedData) {
                        return null;
                    }

                    $eavEntity = $this->repository->find($submittedData);
                    if (!$eavEntity instanceof DataInterface) {
                        throw new TransformationFailedException('Data should be a DataInterface');
                    }
                    $this->checkFamily($eavEntity, $options['allowed_families']);

                    return $eavEntity;
                }
            )
        );

        $builder->resetViewTransformers();
        $builder->addViewTransformer(
            new CallbackTransformer(
                function ($originalData) {
                    if ($originalData instanceof DataInterface) {
                        return $originalData->getId();
                    }

                    return $originalData;
                },
                function ($submittedData) {
                    return $submittedData;
                }
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws UnexpectedValueException
     * @throws MissingFamilyException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'auto_init' => true,
                'max_results' => 0,
                'choices' => [],
                'choice_loader' => null,
            ]
        );
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return SimpleDataSelectorType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_autocomplete_data_selector';
    }

    /**
     * @param DataInterface     $eavEntity
     * @param FamilyInterface[] $allowedFamilies
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    protected function checkFamily(DataInterface $eavEntity, array $allowedFamilies)
    {
        foreach ($allowedFamilies as $family) {
            if ($eavEntity->getFamilyCode() === $family->getCode()) {
                return;
            }
        }

        throw new TransformationFailedException("Family {$eavEntity->getFamilyCode()} is not allowed");
    }
}
