<?php
/* * * * * * * * * * * * * * * * * * * * * * * * *
* Admin panel: User Controller Factory
* * * * * * * * * * * * * * * * * * * * * * * * */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\UserController;
use Zend\Session\Container;

class UserControllerFactory implements FactoryInterface
{
	/* Invoke / include primary required modules for this controller */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
		$adminMsgsession = new Container(ADMIN_AUTH_NAMESPACE);

		$AbstractModel = new \Application\Model\AbstractModel($container->get('Zend\Db\Adapter\Adapter'));	 
		$UserModel = new \Admin\Model\User($container->get('Zend\Db\Adapter\Adapter'));
		$EmailModel = new \Application\Model\Email($container->get('Zend\Db\Adapter\Adapter'));
		$Adapter = $container->get('Zend\Db\Adapter\Adapter');	 

		$site_config_data = $AbstractModel->Super_Get(T_CONFIG,'1=1','fetchAll');		
		foreach($site_config_data as $key => $config){
			$config_data[$config['config_key']] = $config['config_value'] ;
			$config_groups[$config['config_group']][$config['config_key']] = $config['config_value'];	
		} 
		$authService = $container->get('Zend\Authentication\AuthenticationServiceInterface');
		return new UserController($AbstractModel,$UserModel,$Adapter,$adminMsgsession,$config_data,$EmailModel,$authService);
    }
}
