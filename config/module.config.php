<?php
namespace AuthCAS;

use Laminas\Authentication\AuthenticationService;

return [
    'service_manager' => [
        'factories' => [
            'AuthCAS\AuthenticationService' => Service\AuthenticationServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\LoginController::class => Service\Controller\LoginControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'cas/login' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/cas/login',
                    'defaults' => [
                        'controller' => Controller\LoginController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => Controller\LoginController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\ConfigForm::class => Service\Form\ConfigFormFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'authcas' => [
        'config' => [
            'cas_enabled' => 0,
            'cas_server_version' => '2.0',
            'cas_server_hostname' => '',
            'cas_server_port' => 443,
            'cas_server_uri' => '',
            'cas_user_email_attribute' => 'mail',
            'cas_user_name_attribute' => 'uid',
            'cas_accounts_auto_register' => 0,
            'cas_accounts_default_role' => 'researcher',
        ],
    ],
];
