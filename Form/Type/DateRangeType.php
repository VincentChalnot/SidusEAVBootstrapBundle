<?php

namespace Sidus\EAVBootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type to allow picking of a date range
 *
 * This type replaces the default DateRangeType from the FilterBundle that uses outdated widgets
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DateRangeType extends AbstractType
{
    public const START_NAME = 'startDate';
    public const END_NAME = 'endDate';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'empty_data' => [
                    self::START_NAME => null,
                    self::END_NAME => null,
                ],
            ]
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                self::START_NAME,
                DatePickerType::class,
                [
                    'widget_position' => 'left',
                    'attr' => [
                        'class' => 'input-sm form-control',
                        'placeholder' => 'sidus.filter.date_range.start_date',
                    ],
                ]
            )
            ->add(
                self::END_NAME,
                DatePickerType::class,
                [
                    'attr' => [
                        'class' => 'input-sm form-control',
                        'placeholder' => 'sidus.filter.date_range.end_date',
                    ],
                ]
            );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_date_range';
    }
}
