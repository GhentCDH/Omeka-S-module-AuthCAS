<?php
namespace AuthCAS\Adapter;

use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Omeka\Api\Exception\ValidationException;
use Laminas\Authentication\Adapter\AbstractAdapter;
use Laminas\Authentication\Result;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Settings\Settings;
use phpCAS;

/**
 * Auth adapter for checking passwords through CAS.
 */
class CasAdapter extends AbstractAdapter
{
    protected bool $isInitialized = false;

    /**
     * Create the adapter.
     *
     * @param EntityRepository $repository The User repository.
     * @param array $options
     */
    public function __construct(protected ContainerInterface $serviceLocator, protected EntityRepository $repository, array $options = [])
    {
    }

    public function init()
    {
        if ( !$this->isInitialized ) {
            /** @var Settings */
            $settings = $this->getServiceLocator()->get('Omeka\Settings');
            // die($settings->get('cas_server_hostname'));
            try {
// Enable debugging
phpCAS::setLogger();
// Enable verbose error messages. Disable in production!
phpCAS::setVerbose(true);
                phpCAS::client(
                    $settings->get('cas_server_version'),
                    $settings->get('cas_server_hostname'),
                    $settings->get('cas_server_port'),
                    $settings->get('cas_server_uri'),
                    $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'],
                    false
                );
                phpCAS::setNoCasServerValidation();
                $this->isInitialized = true;
            } catch (\Throwable $E) {
                die('error');
            }
        }
    }

    public function authenticate()
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');            

        // authenticate
        try {
            if ( !phpCAS::isAuthenticated() ) {
                return phpCAS::forceAuthentication();
            }

            $uid = phpCAS::getAttribute($settings->get('cas_user_name_attribute'));
            $mail = phpCAS::getAttribute($settings->get('cas_user_email_attribute'));

            $this->identity = $mail;
        } catch (\Throwable $E) {
            return new Result( Result::FAILURE_CREDENTIAL_INVALID, null,
                ['Cas user not found']);
        }

        // add user?
        if ( $settings->get('cas_accounts_auto_register') ) {
            if ( !$user = $this->repository->findOneBy(['email' => $this->identity]) ) {
                $apiManager = $this->getServiceLocator()->get('Omeka\ApiManager');
                try {
                    $response = $apiManager->create('users', [
                        'o:is_active' => true,
                        'o:role' => $settings->get('cas_accounts_default_role'),
                        'o:name' => $uid,
                        'o:email' => $mail,
                    ]);

                    // Set the password.
                    $user = $response->getContent()->jsonSerialize();

                    $entityManager = $this->getServiceLocator()->get('Omeka\EntityManager');
                    $userEntity = $entityManager->find('Omeka\Entity\User', $user['o:id']);
                    $userEntity->setPassword('test');
                    $entityManager->flush();
                } catch (ValidationException $e) {
                    print_r($e);
                }
            }
        }

        // locate user
        $user = $this->repository->findOneBy(['email' => $this->identity]);

        if (!$user || !$user->isActive()) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null,
                ['User not found.']);
        }

        return new Result(Result::SUCCESS, $user);
    }

    /**
     * Get ServiceLocator
     *
     * @return ContainerInterface|ServiceManager
     */
    protected function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
