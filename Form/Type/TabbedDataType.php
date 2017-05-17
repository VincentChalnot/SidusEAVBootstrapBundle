<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Sidus\EAVModelBundle\Form\Type\DataType;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Sidus\EAVModelBundle\Translator\TranslatableTrait;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Same as DataType but with tabs for attribute groups
 */
class TabbedDataType extends DataType
{
    use TranslatableTrait;

    /**
     * @todo Move this logic in a "group" attribute form builder ?
     *
     * {@inheritdoc}
     */
    public function buildValuesForm(FormBuilderInterface $builder, array $options = [])
    {
        /** @var FamilyInterface $family */
        $family = $options['family'];

        $attributes = $family->getAttributes();
        if ($options['attributes_config']) {
            $attributes = [];
            foreach (array_keys($options['attributes_config']) as $attributeCode) {
                $attributes[] = $family->getAttribute($attributeCode);
            }
        }

        foreach ($attributes as $attribute) {
            if (!$attribute->getGroup()) { // First only the attributes with no group
                $this->attributeFormBuilder->addAttribute(
                    $builder,
                    $attribute,
                    $this->resolveAttributeConfig($attribute, $options)
                );
            }
        }

        foreach ($attributes as $attribute) {
            if ($attribute->getGroup()) { // Then only the attributes with a group
                $tabName = '__tab_'.$family->getCode().'_'.$attribute->getGroup();
                if (!$builder->has($tabName)) {
                    $tabOptions = [
                        'label' => $this->getGroupLabel($family, $attribute->getGroup()),
                        'inherit_data' => true,
                    ];
                    $icon = $this->getGroupIcon($family, $attribute->getGroup());
                    if ($icon) {
                        $tabOptions['icon'] = $icon;
                    }
                    $builder->add($tabName, TabType::class, $tabOptions);
                }
                $this->attributeFormBuilder->addAttribute(
                    $builder->get($tabName),
                    $attribute,
                    $this->resolveAttributeConfig($attribute, $options)
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault(
            'attr',
            [
                'novalidate' => 'novalidate',
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_tabbed_data';
    }

    /**
     * Use label from formOptions or use translation or automatically create human readable one
     *
     * @param FamilyInterface $family
     * @param string          $groupName
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getGroupLabel(FamilyInterface $family, $groupName)
    {
        $transKeys = [
            "eav.family.{$family->getCode()}.group.{$groupName}.label",
            "eav.group.{$groupName}.label",
        ];

        return ucfirst($this->tryTranslate($transKeys, [], $groupName));
    }

    /**
     * Use label from formOptions or use translation or automatically create human readable one
     *
     * @param FamilyInterface $family
     * @param string          $groupName
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getGroupIcon(FamilyInterface $family, $groupName)
    {
        $transKeys = [
            "eav.family.{$family->getCode()}.group.{$groupName}.icon",
            "eav.group.{$groupName}.icon",
        ];

        return $this->tryTranslate($transKeys, []);
    }
}
