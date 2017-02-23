<?php

namespace Sidus\EAVBootstrapBundle\Twig;

use Sidus\EAVModelBundle\Configuration\FamilyConfigurationHandler;
use Sidus\EAVModelBundle\Translator\TranslatableTrait;
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
     * @param TranslatorInterface        $translator
     */
    public function __construct(FamilyConfigurationHandler $familyConfigurationHandler, TranslatorInterface $translator)
    {
        $this->familyConfigurationHandler = $familyConfigurationHandler;
        $this->translator = $translator;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sidus_eav_model';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('get_families', [$this->familyConfigurationHandler, 'getFamilies']),
            new Twig_SimpleFunction('get_root_families', [$this->familyConfigurationHandler, 'getRootFamilies']),
            new Twig_SimpleFunction('get_family', [$this->familyConfigurationHandler, 'getFamily']),
            new Twig_SimpleFunction('tryTrans', [$this, 'tryTrans']),
        ];
    }

    /**
     * @param string|array $tIds
     * @param array        $parameters
     * @param string|null  $fallback
     * @param bool         $humanizeFallback
     *
     * @return string
     */
    public function tryTrans($tIds, array $parameters = [], $fallback = null, $humanizeFallback = true)
    {
        return $this->tryTranslate($tIds, $parameters, $fallback, $humanizeFallback);
    }
}
