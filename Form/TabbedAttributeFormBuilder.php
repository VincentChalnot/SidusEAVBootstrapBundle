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
        $level
    ) {
        if (0 !== $level) {
            return parent::buildFieldset($parentBuilder, $attribute, $groupPath, $level);
        }

        $fieldsetCode = '__tab_'.$groupPath[$level];
        if ($parentBuilder->has($fieldsetCode)) {
            return $parentBuilder->get($fieldsetCode);
        }

        $tabOptions = [
            'label' => $this->getGroupLabel($attribute, $groupPath, $level),
            'inherit_data' => true,
        ];
        $icon = $this->getGroupIcon($attribute, $groupPath, $level);
        if ($icon) {
            $tabOptions['icon'] = $icon;
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
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getGroupIcon(AttributeInterface $attribute, array $groupPath, $level)
    {
        $fieldsetPath = implode('.', array_splice($groupPath, 0, $level));
        $family = $attribute->getFamily();

        $transKeys = [
            "eav.family.{$family->getCode()}.group.{$fieldsetPath}.icon",
            "eav.group.{$fieldsetPath}.icon",
        ];

        return $this->tryTranslate($transKeys, []);
    }
}
