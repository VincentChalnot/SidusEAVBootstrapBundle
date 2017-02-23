<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Alternative collection type integrated with bootstrap
 */
class BootstrapCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace(
            $view->vars,
            [
                'allow_add' => $options['allow_add'],
                'allow_delete' => $options['allow_delete'],
                'sub_widget_col' => $options['sub_widget_col'],
                'button_col' => $options['button_col'],
                'prototype_name' => $options['prototype_name'],
                'add_button_text' => $options['add_button_text'],
                'delete_button_text' => $options['delete_button_text'],
                'sortable' => $options['sortable'],
            ]
        );
        if (false === $view->vars['allow_delete']) {
            $view->vars['sub_widget_col'] += $view->vars['button_col'];
        }
        if ($form->getConfig()->hasAttribute('prototype')) {
            $view->vars['prototype'] = $form->getConfig()->getAttribute('prototype')->createView($view);
        }
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     * @throws UndefinedOptionsException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'add_button_text' => 'sidus.collection.add',
                'delete_button_text' => 'sidus.collection.delete',
                'sub_widget_col' => 10,
                'button_col' => 2,
                'sortable' => true,
                'error_bubbling' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sidus_bootstrap_collection';
    }
}
