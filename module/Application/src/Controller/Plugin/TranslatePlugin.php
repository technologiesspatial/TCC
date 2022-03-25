<?php
namespace Application\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Db\Adapter\Adapter;
use Zend\I18n\Translator\Translator;
use Zend\Session\Container;

class TranslatePlugin extends AbstractPlugin
{
    
    protected $translator;
    function __construct()
    {
		$translator = new \Zend\I18n\Translator\Translator(); 
		
		//$translator->setLocale("en");
		//$translator->addTranslationFile("phparray",ROOT_PATH.'/vendor/translations/lang.php',"default","en");
		//$translator->addTranslationFile("phparray",ROOT_PATH.'/module/Application/src/Controller/Plugin/Translations.php',"default","en");
        $this->translator = $translator;
    }
    public function translate($string)
    {
		return $this->translator->translate($string);
    }
	
}
?>