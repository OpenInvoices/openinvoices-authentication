<?php
namespace OpenInvoices\Authentication\Service;

use Interop\Container\ContainerInterface;
use OpenInvoices\Authentication\AuthenticationListener;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthenticationListenerFactory implements FactoryInterface
{
    /**
     * Create and return a request instance.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return AuthenticationListener
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $authenticationService = $container->get(AuthenticationServiceInterface::class);
        return new AuthenticationListener($authenticationService);
    }
}