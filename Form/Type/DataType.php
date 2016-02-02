<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Sidus\EAVModelBundle\Entity\Data;
use Sidus\EAVModelBundle\Form\Type\DataType as BaseDataType;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataType extends BaseDataType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function buildValuesForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Data $data */
        $data = $builder->getData();
        $family = $data->getFamily();
        foreach ($family->getAttributes() as $attribute) {
            if ($attribute->getGroup()) {
                $tabName = '__tab_' . $attribute->getGroup();
                if (!$builder->has($tabName)) {
                    $tabOptions = [
                        'label' => $this->getGroupLabel($family, $attribute->getGroup()),
                        'inherit_data' => true,
                    ];
                    $icon = $this->getGroupIcon($family, $attribute->getGroup());
                    if ($icon) {
                        $tabOptions['icon'] = $icon;
                    }
                    $builder->add($tabName, 'tab', $tabOptions);
                }
                $this->addAttribute($builder->get($tabName), $attribute, $family);
            } else {
                $this->addAttribute($builder, $attribute, $family);
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }

    /**
     * Use label from formOptions or use translation or automatically create human readable one
     *
     * @param FamilyInterface $family
     * @param string $groupName
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getGroupLabel(FamilyInterface $family, $groupName)
    {
        $transKeys = [
            "{$family->getCode()}.group.{$groupName}.label",
            "groups.{$groupName}.label",
        ];
        return $this->translateOrDefault($transKeys, $groupName);
    }

    /**
     * Use label from formOptions or use translation or automatically create human readable one
     *
     * @param FamilyInterface $family
     * @param string $groupName
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getGroupIcon(FamilyInterface $family, $groupName)
    {
        $transKeys = [
            "{$family->getCode()}.group.{$groupName}.icon",
            "groups.{$groupName}.icon",
        ];
        return $this->translateOrDefault($transKeys, $groupName);
    }
}
