<?php
namespace AuthCAS\Service\Controller;

use Psr\Container\ContainerInterface;
use AuthCAS\Controller\LoginController;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LoginControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new LoginController(
            $services->get('Omeka\EntityManager'),
            $services->get('AuthCAS\AuthenticationService')
        );
    }
}
