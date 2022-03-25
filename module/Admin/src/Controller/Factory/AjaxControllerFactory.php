<?php
/* * * * * * * * * * * * * * * * * * * * * * * * *
* Admin panel: Ajax Controller Factory
* * * * * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\AjaxController;
use Zend\Session\Container;


class AjaxControllerFactory implements FactoryInterface
{
	/* Invoke / include primary required modules for this controller */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {	
	   $Adapter = $container->get('Zend\Db\Adapter\Adapter');	  
	   
	   $AbstractModel = new \Application\Model\AbstractModel($container->get('Zend\Db\Adapter\Adapter'));	
	   $EmailModel = new \Application\Model\Email($container->get('Zend\Db\Adapter\Adapter'));	
	  
	   return new AjaxController($Adapter,$AbstractModel,$EmailModel);
    }
}
