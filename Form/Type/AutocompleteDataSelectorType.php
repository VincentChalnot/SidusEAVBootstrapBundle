<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sidus\EAVBootstrapBundle\Form\Helper\ComputeLabelHelper;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Form\Type\SimpleDataSelectorType;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
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

    /** @var ComputeLabelHelper */
    protected $computeLabelHelper;

    /**
     * @param RouterInterface    $router
     * @param ComputeLabelHelper $computeLabelHelper
     * @param Registry           $doctrine
     * @param string             $dataClass
     */
    public function __construct(
        RouterInterface $router,
        ComputeLabelHelper $computeLabelHelper,
        Registry $doctrine,
        $dataClass
    ) {
        $this->router = $router;
        $this->computeLabelHelper = $computeLabelHelper;
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
        if ($data instanceof DataInterface) { // Set the current data in the choices
            $value = $form->getViewData();
            $label = $this->computeLabelHelper->computeLabel($data, $value, $options);
            $view->vars['choices'] = [
                $value => new ChoiceView($data, $value, $label),
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

        $view->vars['attr']['data-query-uri'] = $options['query_uri'];
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
     * @throws \RuntimeException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'auto_init' => true,
                'max_results' => 0,
                'choices' => [],
                'choice_loader' => null,
                'query_uri' => null,
            ]
        );

        $resolver->setNormalizer(
            'query_uri',
            function (Options $options, $value) {
                if (null !== $value) {
                    return $value;
                }
                /** @var AttributeInterface $attribute */
                $attribute = $options['attribute'];
                if (!$attribute) {
                    throw new UnexpectedValueException('Unable to generate API endpoint without an attribute option');
                }
                $family = $attribute->getFamily();
                if (!$family) {
                    throw new UnexpectedValueException(
                        'Unable to generate API endpoint without an attribute option with a family'
                    );
                }
                try {
                    return $this->router->generate(
                        'sidus_autocomplete_api_search',
                        [
                            'attributePath' => $family->getCode().'.'.$attribute->getCode(),
                        ]
                    );
                } catch (ExceptionInterface $e) {
                    throw new \RuntimeException('Unable to generate autocomplete route', 0, $e);
                }
            }
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
