<?php
namespace AuthCAS;
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

use AuthCAS\Form\ConfigForm;
use Doctrine\ORM\EntityManager;
use Omeka\Module\AbstractModule;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AuthenticationService
     */
    protected $auth;

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event): void
    {
        parent::onBootstrap($event);

        $services = $this->getServiceLocator();
        /** @var $settings Settings */
        $settings = $services->get('Omeka\Settings');
        $request_get_params = $event->getRequest()->getQuery()->toArray();

        if ( $settings->get('cas_enabled') == 1 && !isset($request_get_params['omeka']) ) {
            $this->addRoutes();
        }
        $this->addAclRules();
    }

    /**
     * Add ACL rules
     * - allow LoginController to add users and set roles
     */
    protected function addAclRules(): void
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');

        // allow anonymous access to LoginController
        $acl = $services->get('Omeka\Acl');
        $acl->allow(null, [ Controller\LoginController::class ]);

        // allow to create users
        if ( $settings->get('cas_accounts_auto_register') ) {
            $acl->allow(
                null,
                [\Omeka\Entity\User::class],
                // Change role and Activate user should be set to allow external
                // logging (ldap, saml, etc.), not only guest registration here.
                ['create', 'change-role', 'activate-user']
            )
                ->allow(
                    null,
                    [\Omeka\Api\Adapter\UserAdapter::class],
                    ['create']
                );
        }
    }

    /**
     * Add Routes
     * - overwrite login route
     */
    protected function addRoutes(): void
    {
        $serviceLocator = $this->getServiceLocator();
        $router = $serviceLocator->get('Router');

        $router->addRoute('login', [
            'type' => \Laminas\Router\Http\Literal::class,
            'options' => [
                    'route' => '/login',
                    'defaults' => [
                    'controller' => Controller\LoginController::class,
                    'action' => 'login',
                ],
            ],
        ]);
    }

    /**
     * Get Configuration Form
     *
     * @param PhpRenderer $renderer
     * @return string
     */
    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        // prepare form values
        $data = [];
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        foreach ($defaultSettings as $name => $value) {
            $data[$name] = $settings->get($name, $value);
            $data['fieldset_cas_server'][$name] = $settings->get($name, $value);
            $data['fieldset_user_accounts'][$name] = $settings->get($name, $value);
            $data['fieldset_cas_attributes'][$name] = $settings->get($name, $value);
        }

        // init form
        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($data);

        //output
        $view = $renderer;
        $form->prepare();
        return $view->formCollection($form);
    }

    /**
     * Handle Configuration Form
     *
     * @param AbstractController $controller
     * @return bool
     */
    public function handleConfigForm(AbstractController $controller): bool
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        $params = $controller->getRequest()->getPost();

        // init & validate form
        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        // merge fieldset params
        $params = array_merge(
            (array) $params,
            $params['fieldset_cas_server'],
            $params['fieldset_user_accounts'],
            $params['fieldset_cas_attributes'],
        );

        // sanitize
        $params['cas_server_port'] = (int) $params['cas_server_port'];
        $params['cas_accounts_auto_register'] = (int) $params['cas_accounts_auto_register'];
        $params['cas_enabled'] = (int) $params['cas_enabled'];

        // save settings
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        foreach ($params as $name => $value) {
            if (array_key_exists($name, $defaultSettings)) {
                $settings->set($name, $value);
            }
        }

        return true;
    }
}
