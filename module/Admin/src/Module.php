<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Module of all controllers
* * * * * * * * * * * * * * * * * * * * * * * * * */

namespace Admin;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\ModuleManager;

class Module implements ConfigProviderInterface
{
	public function getConfig()
	{
		return include __DIR__ . '/../config/module.config.php';
	}

	public function init(ModuleManager $mm)
    {
    	$mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__,
    	'dispatch', function($e) {
    		$e->getTarget()->layout('layout/admin/layout');
    	});
	}

	public function getControllerConfig()
    {
		
        return [
            'factories' => [
                Controller\IndexController::class => function($container) {
                    return new Controller\IndexController(
                        $container->get(Model\AdminTable::class)
                    );
                },

				Controller\ProfileController::class => function($container) {
                    return new Controller\ProfileController(
                        $container->get(Model\AdminTable::class)
                    );
                },

				Controller\StaticController::class => function($container) {
                    return new Controller\StaticController(
                        $container->get(Model\AdminTable::class)
                    );
                },

                Controller\VideocategoryController::class => function($container) {
                    return new Controller\VideocategoryController(
                        $container->get(Model\AdminTable::class)
                    );
                },

				Controller\AjaxController::class => function($container) {
                    return new Controller\AjaxController(
                        $container->get(Model\AdminTable::class)
                    );
                },

				Controller\ClientController::class => function($container) {
                    return new Controller\ClientController(
                        $container->get(Model\AdminTable::class)
                    );
                },

                Controller\MemberController::class => function($container) {
                    return new Controller\MemberController(
                        $container->get(Model\AdminTable::class)
                    );
                },
				

            ],

        ];

    }

}