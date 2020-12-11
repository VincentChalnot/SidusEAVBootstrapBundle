<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Sidus\BaseBundle\Doctrine\RepositoryFinder;
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
 * Add supports for Select2 autocomplete
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
     * @param RepositoryFinder   $repositoryFinder
     * @param string             $dataClass
     */
    public function __construct(
        RouterInterface $router,
        ComputeLabelHelper $computeLabelHelper,
        RepositoryFinder $repositoryFinder,
        $dataClass
    ) {
        $this->router = $router;
        $this->computeLabelHelper = $computeLabelHelper;
        $this->repository = $repositoryFinder->getRepository($dataClass);
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
        if (\is_iterable($data)) {
            foreach ($data as $datum) {
                $this->appendCurrentChoice($view, $options, $datum);
            }
        } else {
            $this->appendCurrentChoice($view, $options, $data);
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

                    if ($options['scalar_results']) {
                        return $submittedData;
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
                'scalar_results' => false,
            ]
        );
        $resolver->setAllowedTypes('scalar_results', ['bool']);

        $resolver->setNormalizer(
            'query_uri',
            function (Options $options, $value) {
                if (null !== $value) {
                    return $value;
                }
                /** @var AttributeInterface $attribute */
                $attribute = $options['attribute'];
                if (!$attribute) {
                    $allowedFamilies = $options['allowed_families'];
                    if (1 === \count($allowedFamilies)) {
                        try {
                            /** @var FamilyInterface $family */
                            $family = reset($allowedFamilies);

                            return $this->router->generate(
                                'sidus_autocomplete_api_family_search',
                                [
                                    'familyCode' => $family->getCode(),
                                    'choiceLabel' => $options['choice_label'],
                                ]
                            );
                        } catch (ExceptionInterface $e) {
                            throw new \RuntimeException('Unable to generate autocomplete route', 0, $e);
                        }
                    }

                    throw new UnexpectedValueException(
                        'Unable to generate API endpoint without an attribute option or a single allowed family'
                    );
                }
                $family = $attribute->getFamily();
                if (!$family) {
                    throw new UnexpectedValueException(
                        'Unable to generate API endpoint without an attribute option with a family'
                    );
                }
                try {
                    return $this->router->generate(
                        'sidus_autocomplete_api_attribute_search',
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

    /**
     * @param FormView             $view
     * @param array                $options
     * @param DataInterface|scalar $data
     */
    protected function appendCurrentChoice(FormView $view, array $options, $data)
    {
        if ($data instanceof DataInterface) { // Set the current data in the choices
            $label = $this->computeLabelHelper->computeLabel($data, $data->getId(), $options);
            $view->vars['choices'][$data->getId()] = new ChoiceView($data, $data->getId(), $label);
        } elseif ($options['scalar_results'] && $data) {
            $view->vars['choices'][$data] = new ChoiceView($data, $data, $data);
        }
    }
}
