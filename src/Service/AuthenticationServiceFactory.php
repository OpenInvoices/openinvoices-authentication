<?php
/**
 * TODO: This should be moved into a OpenInvoice\Core\Authentication
 */
namespace OpenInvoices\Authentication\Service;

use Interop\Container\ContainerInterface;
use OpenInvoices\Authentication\Adapter\DbTable\CredentialAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * Create and return a request instance.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return \Zend\Authentication\AuthenticationServiceInterface
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $adapter = $container->get(Adapter::class);
        $dbAuthAdapter = new CredentialAdapter($adapter, 'users', 'user_name', 'user_password');
        
        $authenticationService = new AuthenticationService();
        $authenticationService->setAdapter($dbAuthAdapter);
        return $authenticationService;
    }
}