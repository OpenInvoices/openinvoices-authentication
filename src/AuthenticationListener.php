<?php
namespace OpenInvoices\Authentication;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;

class AuthenticationListener extends AbstractListenerAggregate
{
    /**
     * @var Array
     */
    private $authenticationRoute;
    
    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;
    
    
    /**
     * Constructor
     * 
     * @param AuthenticationServiceInterface $authenticationService
     * @param string|null $authenticationRoute
     */
    public function __construct(AuthenticationServiceInterface $authenticationService, $authenticationRoute = null)
    {
        $this->authenticationService = $authenticationService;
        $this->authenticationRoute = (is_string($authenticationRoute) ? trim($authenticationRoute) : "authentication");
    }
    
    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute']);
    }
    
    /**
     * Listen to the "route" event and attempt to route the request
     *
     * If no matches are returned, triggers "dispatch.error" in order to
     * create a 404 response.
     *
     * Seeds the event with the route match on completion.
     *
     * @param  MvcEvent $event
     * @return null|RouteMatch
     */
    public function onRoute(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if (! $routeMatch instanceof RouteMatch) {
            // Can't do anything without a route match
            return;
        }
        
        // No authentication required for allowed routes
        if ($routeMatch->getMatchedRouteName() == $this->authenticationRoute)
            return;
            
        $identity = $this->authenticationService->getIdentity();
        if (! $identity) {
            //redirect to login route...
            $response = $event->getResponse();
            $response->setStatusCode(302);
            $uri = $event->getRouter()->assemble([], ['name' => 'authentication']);
            $response->getHeaders()->addHeaderLine('Location', $uri);
            $event->stopPropagation(true);
            
            return $response;
        }
    }
}
