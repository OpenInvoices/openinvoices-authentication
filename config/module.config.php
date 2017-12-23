<?php
namespace OpenInvoices\Authentication;

use OpenInvoices\Authentication\Service\AuthenticationListenerFactory;
use OpenInvoices\Authentication\Service\AuthenticationServiceFactory;
use Zend\Authentication\AuthenticationServiceInterface;

return [
    'service_manager' => [
        'factories' => [
            AuthenticationServiceInterface::class => AuthenticationServiceFactory::class,
            AuthenticationListener::class => AuthenticationListenerFactory::class
        ],
    ],
    'listeners' => [
        AuthenticationListener::class
    ]
];