<?php

namespace Sidus\EAVBootstrapBundle\Twig;

use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Translator\TranslatableTrait;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class SidusTwigExtension extends Twig_Extension
{
    use TranslatableTrait;

    /** @var FamilyConfigurationHandler */
    protected $familyConfigurationHandler;

    /**
     * @param FamilyConfigurationHandler $familyConfigurationHandler
     * @param TranslatorInterface $translator
     */
    public function __construct(FamilyConfigurationHandler $familyConfigurationHandler, TranslatorInterface $translator)
    {
        $this->familyConfigurationHandler = $familyConfigurationHandler;
        $this->translator = $translator;
    }

    public function getName()
    {
        return 'sidus_eav_model';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('form_has_error', [$this, 'formHasError']),
            new Twig_SimpleFunction('get_families', [$this, 'getFamilies']),
            new Twig_SimpleFunction('tryTrans', [$this, 'tryTrans'])
        ];
    }

    public function formHasError(FormView $form)
    {
        if (0 < count($form->vars['errors'])) {
            return true;
        }
        foreach ($form->children as $child) {
            if ($this->formHasError($child)) {
                return true;
            }
        }
        return false;
    }

    public function getFamilies()
    {
        return $this->familyConfigurationHandler->getFamilies();
    }

    /**
     * @param string|array $tIds
     * @param array $parameters
     * @param string|null $fallback
     * @return string
     */
    public function tryTrans($tIds, array $parameters = [], $fallback = null)
    {
        return $this->tryTranslate($tIds, $parameters, $fallback);
    }
}