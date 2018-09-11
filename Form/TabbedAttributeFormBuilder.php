<?php

namespace Sidus\EAVBootstrapBundle\Form;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * First level groups are rendered as tabs instead of fieldsets
 */
class TabbedAttributeFormBuilder extends BootstrapAttributeFormBuilder
{
    /**
     * @param FormBuilderInterface $parentBuilder
     * @param AttributeInterface   $attribute
     * @param array                $groupPath
     * @param                      $level
     * @param array                $options
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     * @throws \InvalidArgumentException
     *
     * @return FormBuilderInterface
     */
    protected function buildFieldset(
        FormBuilderInterface $parentBuilder,
        AttributeInterface $attribute,
        array $groupPath,
        $level,
        array $options = []
    ) {
        if (0 !== $level) {
            return parent::buildFieldset($parentBuilder, $attribute, $groupPath, $level, $options);
        }

        $fieldsetCode = '__tab_'.$groupPath[$level];
        if ($parentBuilder->has($fieldsetCode)) {
            return $parentBuilder->get($fieldsetCode);
        }

        $tabOptions = [
            'label' => $this->getGroupLabel($attribute->getFamily(), $groupPath, $level),
            'translate_label' => false,
            'inherit_data' => true,
        ];
        $icon = $this->getGroupIcon($attribute, $groupPath, $level);
        if ($icon) {
            $tabOptions['icon'] = $icon;
        }

        $fieldsetPath = $this->getFieldsetPath($groupPath, $level);
        if (isset($options['fieldset_options'][$fieldsetPath])) {
            $tabOptions = array_merge($tabOptions, $options['fieldset_options'][$fieldsetPath]);
        }

        $parentBuilder->add($fieldsetCode, TabType::class, $tabOptions);

        return $parentBuilder->get($fieldsetCode);
    }

    /**
     * Use translations to fetch group icon
     *
     * @param AttributeInterface $attribute
     * @param array              $groupPath
     * @param int                $level
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function getGroupIcon(AttributeInterface $attribute, array $groupPath, $level)
    {
        $fieldsetPath = $this->getFieldsetPath($groupPath, $level);
        $family = $attribute->getFamily();

        $transKeys = [
            "eav.family.{$family->getCode()}.group.{$fieldsetPath}.icon",
            "eav.group.{$fieldsetPath}.icon",
        ];

        return $this->tryTranslate($transKeys, []);
    }
}
