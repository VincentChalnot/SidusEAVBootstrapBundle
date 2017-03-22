<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Entity\DataInterface;
use Sidus\EAVModelBundle\Entity\DataRepository;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnexpectedValueException;

class AutocompleteDataSelectorType extends AbstractType
{
    /** @var string */
    protected $dataClass;

    /** @var DataRepository */
    protected $repository;

    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /**
     * @param string                     $dataClass
     * @param Registry                   $doctrine
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     */
    public function __construct(
        $dataClass,
        Registry $doctrine,
        FamilyConfigurationHandler $familyConfigurationHandler
    ) {
        $this->dataClass = $dataClass;
        $this->repository = $doctrine->getRepository($dataClass);
        $this->familyConfigurationHandler = $familyConfigurationHandler;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['auto_init']) {
            if (empty($view->vars['attr']['class'])) {
                $view->vars['attr']['class'] = '';
            } else {
                $view->vars['attr']['class'] .= ' ';
            }
            $view->vars['attr']['class'] .= 'select2';
            if (!$options['required']) {
                $view->vars['attr']['class'] .= ' force-allowclear';
            }
        }
        $view->vars['allowed_families'] = $options['allowed_families'];
        $view->vars['eavData'] = $form->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new CallbackTransformer(
                function (DataInterface $data = null) {
                    if (null === $data) {
                        return null;
                    }

                    return $data->getId();
                },
                function ($id) {
                    if (null === $id) {
                        return null;
                    }

                    return $this->repository->find($id);
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
                'class' => $this->dataClass,
                'allowed_families' => null,
                'auto_init' => true,
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
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_autocomplete_data_selector';
    }
}
