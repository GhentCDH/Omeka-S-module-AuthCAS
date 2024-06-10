<?php
namespace AuthCAS\Controller;

use Doctrine\ORM\EntityManager;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\Container;

class LoginController extends AbstractActionController
{
    public function __construct(protected EntityManager $entityManager, protected AuthenticationService $auth)
    {
        $this->entityManager = $entityManager;
        $this->auth = $auth;
    }

    /**
     * Login
     *
     * @return \Laminas\Http\Response
     */
    public function loginAction()
    {
        if ($this->auth->hasIdentity()) {
            return $this->redirect()->toRoute('admin');
        }

        $sessionManager = Container::getDefaultManager();
        $sessionManager->regenerateId();
        $adapter = $this->auth->getAdapter();
        $result = $this->auth->authenticate();
        if ($result->isValid()) {
            $this->messenger()->addSuccess('Successfully logged in'); // @translate
            $eventManager = $this->getEventManager();
            $eventManager->trigger('user.login', $this->auth->getIdentity());
            $session = $sessionManager->getStorage();
            if ($redirectUrl = $session->offsetGet('redirect_url')) {
                return $this->redirect()->toUrl($redirectUrl);
            }
            return $this->redirect()->toRoute('admin');
        } else {
            $this->messenger()->addError('Could not find account for user '.$adapter->getIdentity()); // @translate
        }
    }

    /**
     * Logout
     *
     * @return \Laminas\Http\Response
     */
    public function logoutAction()
    {
        $this->auth->clearIdentity();

        $sessionManager = Container::getDefaultManager();

        $eventManager = $this->getEventManager();
        $eventManager->trigger('user.logout');

        $sessionManager->destroy();

        $this->messenger()->addSuccess('Successfully logged out'); // @translate
        return $this->redirect()->toRoute('top');
    }
}
