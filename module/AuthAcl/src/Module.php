<?php
namespace AuthAcl;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Mvc\Controller\AbstractActionController;
use AuthAcl\Service\AuthManager;
use AuthAcl\Service\AuthAdapter;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}