<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Enabled a simple bootstrap datepicker on datetime widgets
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DateTimePickerType extends AbstractType
{
    /** @var string */
    protected $locale;

    /**
     * @param string $locale
     */
    public function __construct(string $locale = null)
    {
        $this->locale = $locale;
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['locale'] = $options['locale'];
        $view->vars['format'] = $options['format'];
        $view->vars['widget_position'] = $options['widget_position'];
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'widget' => 'single_text',
                'widget_position' => 'right',
                'html5' => false,
                'locale' => $this->locale,
                'format' => null,
                'date_type' => \IntlDateFormatter::SHORT,
                'time_type' => \IntlDateFormatter::SHORT,
            ]
        );
        $resolver->setNormalizer(
            'format',
            function (Options $options, $value) {
                if (null !== $value) {
                    return $value;
                }
                $locale = $options['locale'] ?: 'en';
                $formatter = \IntlDateFormatter::create(
                    $locale,
                    $options['date_type'],
                    $options['time_type']
                );

                return $formatter->getPattern();
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DateTimeType::class;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_datetime_picker';
    }
}
