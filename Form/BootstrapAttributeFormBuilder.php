<?php

namespace Sidus\EAVBootstrapBundle\Form;

use Sidus\EAVModelBundle\Form\AttributeFormBuilder;
use Sidus\EAVModelBundle\Model\AttributeInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Overrides base attribute form builder to integrate mopa bootstraps options
 */
class BootstrapAttributeFormBuilder extends AttributeFormBuilder
{
    /**
     * @param FormBuilderInterface $parentBuilder
     * @param AttributeInterface   $attribute
     * @param array                $groupPath
     * @param int                  $level
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
        $fieldsetOptions = [
            'label' => $this->getGroupLabel($attribute->getFamily(), $groupPath, $level),
            'translate_label' => false,
            'inherit_data' => true,
            'show_child_legend' => true,
            'horizontal_input_wrapper_class' => '',
            'widget_form_group_attr' => [
                'class' => '',
            ],
        ];

        $fieldsetPath = $this->getFieldsetPath($groupPath, $level);
        if (isset($options['fieldset_options'][$fieldsetPath])) {
            $fieldsetOptions = array_merge($fieldsetOptions, $options['fieldset_options'][$fieldsetPath]);
        }
        $options['fieldset_options'][$fieldsetPath] = $fieldsetOptions;

        return parent::buildFieldset($parentBuilder, $attribute, $groupPath, $level, $options);
    }
}
