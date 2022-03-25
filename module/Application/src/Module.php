<?php

namespace Application;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Session\Container;
use Zend\Session\SessionManager;

use Application\Controller\Factory\IndexControllerFactory;
use Application\Controller\IndexController;
use Application\Service\Factory\AuthenticationServiceFactory;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\Db\Sql\Expression;
use Zend\I18n\Translator\Translator;


class Module implements ConfigProviderInterface, ServiceProviderInterface,ControllerProviderInterface
{
    const VERSION = '3.0.2dev';

	public function init(ModuleManager $mm)
    {
        $mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__,
        'dispatch', function($e) {

        	$sm = $e->getApplication()->getServiceManager();
			$router = $sm->get('router');
			$request = $sm->get('request');
			
			$matchedRoute = $router->match($request);
			$params = $matchedRoute->getParams();

			$CheckController = explode('\\', $params['controller']);
			$CheckModule = $CheckController[0];
			if(isset($CheckController[2])){
				$CheckController = strtolower($CheckController[2]);
			}
			$CheckAction = strtolower($params['action']);
			
			if($CheckController=='staticcontroller' && $CheckAction=='advertisewithusrequest'){
				// popup modal
				$e->getTarget()->layout('layout/modallayout');
			} else {
				$e->getTarget()->layout('layout/layout');	
			}
           
        });
    }
	
	public function onBootstrap(MvcEvent $e)
	{
		$application = $e->getApplication();
		
        $serviceManager = $application->getServiceManager();
       
       
        $sessionManager = $serviceManager->get('Zend\Session\SessionManager');
		
		$container = $e->getApplication()->getServiceManager();
		$authService = $container->get('Zend\Authentication\AuthenticationServiceInterface');
		
		$eventManager   = $e->getApplication()->getEventManager();
		$eventManager->attach(MvcEvent::EVENT_DISPATCH, function (MvcEvent $e) use ($container) {
            $match = $e->getRouteMatch();
            $authService = $container->get('Zend\Authentication\AuthenticationServiceInterface');
            $routeName = $match->getMatchedRouteName();
			$e->getApplication()->getMvcEvent()->getViewModel()->setVariables($match->getParams());
			
            if ($authService->hasIdentity()) {
                return;
            } else if (strpos($routeName, 'admin') !== false) {
               
				return;
            }
        }, 100);
		 
		
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);		
		$eventManager->attach('dispatch', array($this, 'loadConfiguration' ));	
       
		 
		
		$sm = $e->getApplication()->getServiceManager();
		$router = $sm->get('router');
		$request = $sm->get('request');
		$response = $e->getResponse();	
		
		$matchedRoute = $router->match($request);
		if(!empty($matchedRoute)){
			$params = $matchedRoute->getParams();
			$controller = $params['controller'];
			$action = $params['action'];
		}		

		if((isset($_SESSION['SC_key']) && $_SESSION['SC_time'] < date("Y-m-d H:i:s")) || !isset($_SESSION['SC_time']) ){  
			$_SESSION['SC_key'] = randomPassword();
			$_SESSION['SC_time'] = date("Y-m-d H:i:s",strtotime("+60 Minute"));
		}
		
		if($matchedRoute){
			//$params = $matchedRoute->getParams();
			$namespace=$params['__NAMESPACE__'];
		}
		$ipl = get_client_ip();
		if($ipl != 'qwert') {
			
		} else {
		if($action == 'comingsoon' || $action == 'newsletter') {	
		} else {
			$error_url = APPLICATION_URL.'/coming-soon';
			header('Location: '.$error_url);
			exit;
		} }
		
        if(!$matchedRoute){	
			
			if(isset($controller) && isset($action))
           	 $logText = 'The requested controller '.$controller.' was unable to dispatch the request : '.$action.'Action';
			
			$error_url = APPLICATION_URL.'/errorpage';			
			if(in_array(BACKEND,explode('/',$_SERVER['REQUEST_URI']))){
				$error_url = ADMIN_APPLICATION_URL.'/errorpage';		
			}
			
			header('Location: '.$error_url);
        }
		
		if($namespace!='Admin\Controller'){
			
			if($authService->hasIdentity()){
				 $loggedUser = $authService->getIdentity();
			
				 $AbstractModel=new \Application\Model\AbstractModel($sm->get('Zend\Db\Adapter\Adapter'));
				 if(isset($loggedUser->isLoggedbyAdmin) && $loggedUser->isLoggedbyAdmin=='1'){
				 $getclientWhere=T_CLIENT_VAR."client_id=:clientid";
				 $userData =$AbstractModel->Super_Get(T_CLIENTS,$getclientWhere,"fetch",array("warray"=>array("clientid"=>$loggedUser->yurt90w_client_id)));
				 }else{
					 $getclientWhere=T_CLIENT_VAR."client_id=:clientid and ".T_CLIENT_VAR."client_status=:clientstatus";
				 $userData =$AbstractModel->Super_Get(T_CLIENTS,$getclientWhere,"fetch",array("warray"=>array("clientid"=>$loggedUser->yurt90w_client_id,"clientstatus"=>'1')));
				}
				  if(!$userData){
					  if($action != 'logout'){
						$url = APPLICATION_URL.'/logout';
						header('Location: '.$url);
						exit;
					  }
				 } 
				 if(isset($loggedUser->isLoggedbyAdmin) && $loggedUser->isLoggedbyAdmin=='1'){
					 $userData["isLoggedbyAdmin"]=1;
				 	$loggedUser=(object)$userData;
				 }
				
				 $authService->getStorage()->write((object)$userData);
				  
			}
		}
		
		$this->setRedirects($params,$authService,$e);	
		global $AbstractModel,$currentRouteName;
		$AbstractModel=new \Application\Model\AbstractModel($sm->get('Zend\Db\Adapter\Adapter'));
		$currentRouteName=$matchedRoute->getmatchedRouteName();
		
		// TRANSLATOR
		$translator = new \Zend\I18n\Translator\Translator();
		$translator->setLocale("en");
		$translator->addTranslationFile("phparray",ROOT_PATH.'/vendor/lang.php',"default","en"); 
		
		
		$site_config_data=$AbstractModel->Super_Get(T_CONFIG,'1=1','fetchAll');	
		
		foreach($site_config_data as $key=>$config){
			$config_data[$config['config_key']]= $config['config_value'] ;
			$config_groups[$config['config_group']][$config['config_key']]=$config['config_value'];	
		}
		
		
		$viewModel = $e->getViewModel();	
		$viewModel->setVariables(
			array(
				'translator'=>$translator,
				'SITE_CONFIG'=>$config_data,
				'loggedUser'=>$authService->getIdentity(),
				'AbstractModel'=>$AbstractModel,
			)
		);	
    	return $viewModel;
	}
	
	
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
	
	 public function getAutoloaderConfig()
    {
        return array(
		
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/autoload_classmap.php',				
			),
        );
    }
	
	public function getServiceConfig()
    {
        return [
            'aliases' => [
                AuthenticationService::class => AuthenticationServiceInterface::class
            ],
            'factories' => [
                AuthenticationServiceInterface::class => AuthenticationServiceFactory::class
            ]
        ];
    }
	
	public function setRedirects($params,$authService,$e)
    { 
	
	
		global $_allowed_resources ;
		
		$CheckController = explode('\\', $params['controller']);
		$CheckModule = $CheckController[0];
		if(isset($CheckController[2]))
			$CheckController = $CheckController[2];
		$CheckAction = $params['action'];
		
		$site_nameArr = explode("/",$_SERVER['REQUEST_URI']);
		
		if($CheckModule=='Admin' || in_array(BACKEND_NAME,$site_nameArr)){
			
			$session = new Container(ADMIN_AUTH_NAMESPACE);	
			$loggedUser = (object)$session['adminData'];
			
		}else{
			$loggedUser=$authService->getIdentity();
		}
		$arr = (array)$loggedUser;
		if(empty($arr)){
			
 			if(!in_array($CheckController,$_allowed_resources[$CheckModule])){ 
				if(isset($_allowed_resources[$CheckModule][$CheckController]) and is_array($_allowed_resources[$CheckModule][$CheckController])){
					if(in_array($CheckAction,$_allowed_resources[$CheckModule][$CheckController])){
						return ;							
					}
				}
				
				$site_name = explode("/",SITE_HTTP_URL);
				if($CheckModule=='Admin' || in_array(BACKEND_NAME,$site_nameArr)){
					
					$exploder = $CheckModule=='Admin'?BACKEND_NAME:array_pop($site_name);
					$exploder = $exploder==BACKEND?"/".BACKEND:"";
					
					$exploder = BACKEND_NAME;
					$redirect_url=explode(array_pop($site_name),$_SERVER['REQUEST_URI']);
					
					$redirect_url = explode($exploder,$_SERVER['REQUEST_URI']) ;
					if(empty($redirect_url[1])) {
						$url = ADMIN_APPLICATION_URL.'/login';
					} else {
						$url = ADMIN_APPLICATION_URL.'/login?url='.urlencode($redirect_url[1]);
					}
					header('Location: '.$url);
					exit ;
				}
				else{ 
					$exploder =array_pop($site_name);
					$redirect_url = explode($exploder,$_SERVER['REQUEST_URI']) ;
					$url = APPLICATION_URL.'/login?url='.urlencode($redirect_url[1]);
					if(!((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest')|| (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST'))){
					header('Location: '.$url);
					exit;
					}else{
					$url = APPLICATION_URL.'/login';
					echo '<script>window.location.href="'.$url.'"</script>';exit;
					}
				}
			}
			return ;
			
			
		}
		else{
			
				global $_blocked_resources;
				$url = APPLICATION_URL.'/';
				
				if(isset($loggedUser->yurt90w_client_type) && is_array($_blocked_resources[$loggedUser->yurt90w_client_type]))
				{
					foreach($_blocked_resources[$loggedUser->yurt90w_client_type] as $key=>$value)
					{
						
						if(count($_blocked_resources[$loggedUser->yurt90w_client_type][$key])==0)
						{
							if($CheckController==$key)
							{
								header('Location: '.$url);
								exit;
							}
	
						}
						if($key==$CheckController)
						{
							
							foreach($value as $subValues)
							{
								if($CheckAction==$subValues)
								{
									header('Location: '.$url);
									exit;
								}	
							}
						}
					}
				}
				
		}
		return ;
	}
	
	public function loadConfiguration(MvcEvent $e)
    {
		
		
		$container = $e->getApplication()->getServiceManager();
		$authService = $container->get('Zend\Authentication\AuthenticationServiceInterface');  
	
		$controller = $e->getTarget();
		$viewModel = $e->getViewModel();		
		
		$sm = $e->getApplication()->getServiceManager();
		$router = $sm->get('router');
		$request = $sm->get('request');
		
		$matchedRoute = $router->match($request);
		$params = $matchedRoute->getParams();
	
		//Pass db controller and action in layout 
		$explode_controller = explode('\\', $params['controller']);	
		$current_controller = array_pop($explode_controller);
		$current_module = $explode_controller[0];
		$controller->layout()->NAMESPACE = $params['__NAMESPACE__'];
		$controller->layout()->CURRENT_MODULE =$current_module;	
		$controller->layout()->CURRENT_CONTROLLER =$current_controller;
		$controller->layout()->CURRENT_ACTION =$params['action'];	
		$controller->layout()->authService =$authService;	
		//Pass db controller and action in layout 
				
		$response = $e->getResponse();	
		$adapter = $sm->get('Zend\Db\Adapter\Adapter');
		//Pass db adapter in layout 
		$controller->layout()->DB_ADAPTER =$sm->get('Zend\Db\Adapter\Adapter');	
		
		//Pass db adapter in layout 
		$AbstractModel=new \Application\Model\AbstractModel($sm->get('Zend\Db\Adapter\Adapter'));	
		$namespace=$params['__NAMESPACE__'];
		$controller=$params['controller'];
		$action=$params['action'];
		
		
		
		
		//For Admin
		$userData=array();
		if($namespace=='Admin\Controller')
		{		 
			$session = new Container(ADMIN_AUTH_NAMESPACE);	
			$userData = $session['adminData'];

			if(!$session['adminData']){
				if($controller!='Admin\Controller\IndexController'){
					$admin_url = ADMIN_APPLICATION_URL.'/admin-logout';				
					$response->setHeaders($response->getHeaders()
					->addHeaderLine('Location', $admin_url));	
					$response->setStatusCode(302);	
				}
				else if($controller=='index' && $action=='dashboard'){
					$admin_url = ADMIN_APPLICATION_URL.'/admin-logout';				
					$response->setHeaders($response->getHeaders()
					->addHeaderLine('Location', $admin_url));	
					$response->setStatusCode(302);	
				}
			}else{
				 $AbstractModel=new \Application\Model\AbstractModel($sm->get('Zend\Db\Adapter\Adapter'));		
				 $userData =$AbstractModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$session['adminData'][T_CLIENT_VAR.'client_id']."'","fetch");
			}
			
		}
		else
		{
			
			if($controller=='Index' && $action!='index'){
				if(!$authService->hasIdentity())
				{
					$url = APPLICATION_URL.'/logout';
					$response->setHeaders($response->getHeaders()
					->addHeaderLine('Location', $url));
					$response->setStatusCode(302);
				}
			}
			
			if($authService->hasIdentity() && !empty($loggedUser->yurt90w_client_id)){
				$loggedUser = $authService->getIdentity();
				 $AbstractModel=new \Application\Model\AbstractModel($sm->get('Zend\Db\Adapter\Adapter'));		
				 $userData =$AbstractModel->Super_Get(T_CLIENTS,T_CLIENT_VAR."client_id='".$loggedUser->yurt90w_client_id."' and ".T_CLIENT_VAR."client_status='1' and ".T_CLIENT_VAR."client_email_verified='1'","fetch");

				 if(!$userData){
					$url = APPLICATION_URL.'/logout';
					header('Location: '.$url);
					exit;
				 }
				 $authService->getStorage()->write((object)$userData);
			}
		}
		
		
		$viewModel->setVariables(
			array(
				'NAMESPACE'=>$params['__NAMESPACE__'],
				'CURRENT_CONTROLLER'=>$current_controller,
				'CURRENT_ACTION'=>$params['action'],											
				'AbstractModel'=>$AbstractModel,
				'loggedUser'=>$authService->getIdentity(),
				'url_params'=>$params,	
				'authService'=>$authService,
				'currentRoute'=>$matchedRoute->getmatchedRouteName()
			)
		);	
		
    	return $viewModel;
	}

	public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\IndexController::class => function($container) {
                    return new Controller\IndexController(
                        $container->get(Model\AlbumTable::class)
                    );
                },
				Controller\StaticController::class => function($container) {
                    return new Controller\StaticController(
                        $container->get(Model\AdminTable::class)
                    );
                },
            ],
        ];
    }
}