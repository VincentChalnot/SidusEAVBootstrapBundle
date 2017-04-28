<?php

namespace Sidus\EAVBootstrapBundle\Form\Helper;

use Sidus\EAVModelBundle\Entity\DataInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ComputeLabelHelper
{
    /** @var PropertyAccessorInterface */
    protected $accessor;

    /**
     * @param PropertyAccessorInterface $accessor
     */
    public function __construct(PropertyAccessorInterface $accessor)
    {
        $this->accessor = $accessor;
    }

    /**
     * @param DataInterface $data
     * @param mixed         $value
     * @param array         $options
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    public function computeLabel(DataInterface $data, $value, array $options)
    {
        $choiceLabel = array_key_exists('choice_label', $options) ? $options['choice_label'] : null;
        if (null === $choiceLabel) {
            return (string) $data;
        }
        if (is_string($choiceLabel)) {
            try {
                return (string) $this->accessor->getValue($data, $choiceLabel);
            } catch (\Exception $e) {
                throw new \UnexpectedValueException(
                    "Unable to compute label with given 'choice_label' {$choiceLabel}",
                    0,
                    $e
                );
            }
        }
        if (is_callable($choiceLabel)) {
            return (string) $choiceLabel($data, $value, $options);
        }

        throw new \UnexpectedValueException("Unable to compute label with unknown 'choice_label' option");
    }
}
