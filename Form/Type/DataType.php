<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Sidus\EAVModelBundle\Entity\Data;
use Sidus\EAVModelBundle\Form\Type\DataType as BaseDataType;
use Sidus\EAVModelBundle\Model\FamilyInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DataType extends BaseDataType
{
    /**
     * @param Data $data
     * @param FormInterface $form
     * @param array $options
     * @throws \Exception
     */
    public function buildValuesForm(Data $data, FormInterface $form, array $options)
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
