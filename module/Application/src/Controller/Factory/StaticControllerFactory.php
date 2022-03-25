<?php
namespace Application\Controller\Factory;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\StaticController;
use Zend\Session\Container;
use Zend\Authentication\AuthenticationService;
use Zend\Session\SessionManager;
use Zend\Authentication\AuthenticationServiceInterface;

class StaticControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {	   
	   $AbstractModel=new \Application\Model\AbstractModel($container->get('Zend\Db\Adapter\Adapter'));	 
	  
	   $EmailModel=new \Application\Model\Email($container->get('Zend\Db\Adapter\Adapter'));	
	   $FrontMsgsession = new Container(ADMIN_MSG_AUTH_NAMESPACE);
	   $front_Session = new Container(DEFAULT_AUTH_NAMESPACE);
	   $site_config_data=$AbstractModel->Super_Get(T_CONFIG,'1=1','fetchAll');	
	   foreach($site_config_data as $key=>$config){
			$config_data[$config['config_key']]= $config['config_value'] ;
			$config_groups[$config['config_group']][$config['config_key']]=$config['config_value'];	
	   }
	   $Adapter=$container->get('Zend\Db\Adapter\Adapter');	
	   $authService = $container->get('Zend\Authentication\AuthenticationServiceInterface');	  
	   return new StaticController($AbstractModel,$EmailModel,$FrontMsgsession,$front_Session,$config_data,$authService);
    }
	
}




