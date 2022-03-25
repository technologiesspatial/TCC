<?php
namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;
use Zend\Authentication\Storage\Session as SessionStorage;

class AuthenticationServiceFactory
{
    // pegar adaptador de banco de dados
    // configurar um adaptador para administrar a autenticação do usuário
    // cria a sessão para guardamos o usuário
    // criar o serviço de AuthenticationService
    public function __invoke(ContainerInterface $container)
    {
		
        $passwordCallbackVerify = function ($passwordInDatabase, $passwordSent) {
            return password_verify($passwordSent, $passwordInDatabase);
        };
		$emailName='';
		if(isset($_POST['client_email']))
			$emailName=strtolower($_POST['client_email']);
        $dbAdapter = $container->get(AdapterInterface::class);
        $authAdapter = new CallbackCheckAdapter($dbAdapter, T_CLIENTS,stripos($emailName,'@')?T_CLIENT_VAR.'client_email':T_CLIENT_VAR.'client_email', T_CLIENT_VAR.'client_password'/*, $passwordCallbackVerify*/);
        $storage = new Session();
		
		$sessionManager = $container->get(SessionManager::class);
		$authStorage = new SessionStorage(DEFAULT_AUTH_NAMESPACE, 'session', $sessionManager);
		
        return new AuthenticationService($authStorage, $authAdapter);
    }
}