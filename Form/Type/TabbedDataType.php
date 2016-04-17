<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Sidus\EAVModelBundle\Entity\Data;
use Sidus\EAVModelBundle\Form\Type\DataType as DataType;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TabbedDataType extends DataType
{
    /**
     * @inheritdoc
     */
    public function buildValuesForm(FormInterface $form, FamilyInterface $family, Data $data = null, array $options = [])
    {
        $family = $data->getFamily();
        foreach ($family->getAttributes() as $attribute) {
            if ($attribute->getGroup()) {
                $tabName = '__tab_' . $attribute->getGroup();
                if (!$form->has($tabName)) {
                    $tabOptions = [
                        'label' => $this->getGroupLabel($family, $attribute->getGroup()),
                        'inherit_data' => true,
                    ];
                    $icon = $this->getGroupIcon($family, $attribute->getGroup());
                    if ($icon) {
                        $tabOptions['icon'] = $icon;
                    }
                    $form->add($tabName, 'tab', $tabOptions);
                }
                $this->addAttribute($form->get($tabName), $attribute, $family);
            } else {
                $this->addAttribute($form, $attribute, $family);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('attr', [
            'novalidate' => 'novalidate',
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
            "eav.family.{$family->getCode()}.group.{$groupName}.label",
            "eav.group.{$groupName}.label",
        ];
        return ucfirst($this->tryTranslate($transKeys, [], $groupName));
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
            "eav.family.{$family->getCode()}.group.{$groupName}.icon",
            "eav.group.{$groupName}.icon",
        ];
        return $this->tryTranslate($transKeys, []);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sidus_tabbed_data';
    }
}
