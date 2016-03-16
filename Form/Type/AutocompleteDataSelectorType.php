<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Exception\MissingFamilyException;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\AbstractType;
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

    /** @var EntityRepository */
    protected $repository;

    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /**
     * @param $dataClass
     * @param EntityRepository $repository
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     */
    public function __construct($dataClass, EntityRepository $repository, FamilyConfigurationHandler $familyConfigurationHandler)
    {
        $this->dataClass = $dataClass;
        $this->repository = $repository;
        $this->familyConfigurationHandler = $familyConfigurationHandler;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($options['auto_init']) {
            if (empty($view->vars['attr']['class'])) {
                $view->vars['attr']['class'] = 'select2';
            } else {
                $view->vars['attr']['class'] .= ' select2';
            }
        }
        $view->vars['attr']['data-placeholder'] = $options['placeholder'];
        $view->vars['family'] = $options['family'];
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $qb = $this->repository->createQueryBuilder('d');
        $qb->innerJoin('d.values', 'v');

        /** @var FamilyInterface $family */
        $family = $options['family'];
        if ($family) {
            $qb->andWhere('d.family = :family')
                ->andWhere('v.attributeCode = :attributeCode')
                ->setParameter('attributeCode', $family->getAttributeAsLabel()->getCode())
                ->setParameter('family', $family->getCode());
        }
        $builder->setAttribute('query-builder', $qb);
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws UnexpectedValueException
     * @throws MissingFamilyException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => $this->dataClass,
            'search_fields' => ['v.stringValue'],
            'template' => 'SidusEAVModelBundle:Data:data_autocomplete.html.twig',
            'family' => null,
            'auto_init' => true,
        ]);

        $resolver->setNormalizer('family', function (Options $options, $value) {
            if (null === $value) {
                return null;
            }
            if ($value instanceof FamilyInterface) {
                return $value;
            }
            return $this->familyConfigurationHandler->getFamily($value);
        });
        $resolver->setNormalizer('disabled', function (Options $options, $value) {
            if (null === $options['family']) {
                return true;
            }
            return $value;
        });
    }

    public function getParent()
    {
        return 'autocomplete';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sidus_autocomplete_data_selector';
    }
}
