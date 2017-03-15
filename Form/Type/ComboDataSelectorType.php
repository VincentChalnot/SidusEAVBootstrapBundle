<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Form\Type\FamilySelectorType;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Allow selection of family before displaying an autocomplete box
 */
class ComboDataSelectorType extends AbstractType
{
    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /**
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     */
    public function __construct(FamilyConfigurationHandler $familyConfigurationHandler)
    {
        $this->familyConfigurationHandler = $familyConfigurationHandler;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     * @throws \InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'family',
            FamilySelectorType::class,
            [
                'label' => false,
                'families' => $options['allowed_families'],
                'empty_value' => 'sidus.family.selector.empty_value',
            ]
        );
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getForm();
                /** @var DataInterface $data */
                $data = $event->getData();

                /** @var FamilyInterface[] $families */
                $families = $options['allowed_families'];
                foreach ($families as $family) {
                    $selected = false;
                    if ($data instanceof DataInterface) {
                        $selected = $family->getCode() === $data->getFamilyCode();
                    }
                    $form->add(
                        'data_'.$family->getCode(),
                        AutocompleteDataSelectorType::class,
                        [
                            'label' => false,
                            'family' => $family,
                            'auto_init' => $selected,
                            'attr' => [
                                'data-family' => $family->getCode(),
                            ],
                        ]
                    );
                }
            }
        );

        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($originalData) {
                    if ($originalData instanceof DataInterface) {
                        return [
                            'family' => $originalData->getFamily(),
                            'data_'.$originalData->getFamilyCode() => $originalData,
                        ];
                    }

                    return $originalData;
                },
                function ($submittedData) {
                    $family = $submittedData['family'];
                    if ($family instanceof FamilyInterface) {
                        return $submittedData['data_'.$family->getCode()];
                    }

                    return null;
                }
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Exception
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'allowed_families' => null,
            ]
        );
        $resolver->setAllowedTypes('allowed_families', 'array');
        $resolver->setNormalizer(
            'allowed_families',
            function (Options $options, $values) {
                if (null === $values) {
                    $values = $this->familyConfigurationHandler->getFamilies();
                }
                $families = [];
                foreach ($values as $value) {
                    if (!$value instanceof FamilyInterface) {
                        $value = $this->familyConfigurationHandler->getFamily($value);
                    }
                    if ($value->isInstantiable()) {
                        $families[$value->getCode()] = $value;
                    }
                }

                return $families;
            }
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_combo_data_selector';
    }
}
