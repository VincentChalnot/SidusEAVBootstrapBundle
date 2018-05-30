<?php

namespace Sidus\EAVBootstrapBundle\Form\Extension;

use Mopa\Bundle\BootstrapBundle\Form\Type\TabsType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Override MopaBootstrapBundle to add the "valid" information on form tabs line 110
 *
 * This class would probably benefit from an entire refactoring with a much simpler system
 *
 * Extension for Adding Tabs to Form type.
 */
class TabbedFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param array                $options
     */
    public function __construct(FormFactoryInterface $formFactory, array $options)
    {
        $this->formFactory = $formFactory;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'tabs_class' => $this->options['class'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['tabbed'] = false;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!$view->vars['tabbed']) {
            return;
        }

        $activeTab = null;
        $tabIndex = 0;
        $foundInvalid = false;
        $tabs = [];

        foreach ($view->children as $child) {
            if (\in_array('tab', $child->vars['block_prefixes'], true)) {
                $child->vars['tab_index'] = $tabIndex;
                $valid = $child->vars['valid'];

                if ((null === $activeTab || !$valid) && !$foundInvalid) {
                    $activeTab = $child;
                    $foundInvalid = !$valid;
                }

                $tabs[$tabIndex] = $child->vars; // ACTUAL OVERRIDE
                $tabs[$tabIndex]['active'] = false;

                ++$tabIndex;
            }
        }

        $activeTab->vars['tab_active'] = true;
        $tabs[$activeTab->vars['tab_index']]['active'] = true;

        $tabsForm = $this->formFactory->create(
            TabsType::class,
            null,
            [
                'tabs' => $tabs,
                'attr' => [
                    'class' => $options['tabs_class'],
                ],
            ]
        );

        $view->vars['tabs'] = $tabs;
        $view->vars['tabbed'] = true;
        $view->vars['tabsView'] = $tabsForm->createView();
    }
}
