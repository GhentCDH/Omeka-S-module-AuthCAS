<?php
namespace AuthCAS\Form;

use Omeka\Form\Element\PropertySelect;
use Omeka\Permissions\Acl;
use Omeka\Settings\Settings;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\I18n\Translator\TranslatorAwareTrait;
use Laminas\I18n\Translator\TranslatorAwareInterface;

class ConfigForm extends Form implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    protected $local_storage = '';
    protected $allow_unicode = false;

    /**
     * @var Acl
     */
    protected $acl;

    /**
     * @var Settings
     */

    public function init()
    {
        $this->setAttribute('id', 'config-form');

        $this->add([
            'name' => 'cas_enabled',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => $this->translate("Enable cas authentication"),
                'info' => $this->translate("If checked, CAS will be the default authentication method. You can still use local authentication by using the url /login?omeka."),
            ],
            'attributes' => [
                'id' => 'cas_enabled',
            ],
        ]);

        // CAS Server
        $this->add([
            'type' => Fieldset::class,
            'name' => 'fieldset_cas_server',
            'options' => [
                'label' => $this->translate('CAS Server'),
            ],
        ]);
        $fieldsetCasServer = $this->get('fieldset_cas_server');

        $fieldsetCasServer->add([
            'name' => 'cas_server_version',
            'type' => Element\Select::class,
            'options' => [
                'label' => $this->translate("Version"),
                'value_options' => [
                    '1.0' => '1.0',
                    '2.0' => '2.0',
                    '3.0' => '3.0 of higher',
                ]
            ],
            'attributes' => [
                'id' => 'cas_version',
                'required' => true,
            ],
        ]);

        $fieldsetCasServer->add([
            'name' => 'cas_server_hostname',
            'type' => Element\Text::class,
            'options' => [
                'label' => $this->translate("Hostname"),
                'info' => $this->translate("Hostname or IP Address of the CAS server. CAS logins will not work until this hostname is entered."),
            ],
            'attributes' => [
                'id' => 'cas_hostname',
                'required' => true,
            ],
        ]);
        $fieldsetCasServer->add([
            'name' => 'cas_server_port',
            'type' => Element\Text::class,
            'options' => [
                'label' => $this->translate("Port"),
                'info' => $this->translate("443 is the standard SSL port"),
            ],
            'attributes' => [
                'id' => 'cas_port',
                'required' => true,
            ],
        ]);
        $fieldsetCasServer->add([
            'name' => 'cas_server_uri',
            'type' => Element\Text::class,
            'options' => [
                'label' => $this->translate("Base uri"),
                'info' => $this->translate("If CAS is not at the root of the host, include a base uri (e.g., /cas)."),
            ],
            'attributes' => [
                'id' => 'cas_uri',
            ],
        ]);

        // CAS Attributes
        $this->add([
            'type' => Fieldset::class,
            'name' => 'fieldset_cas_attributes',
            'options' => [
                'label' => $this->translate('CAS Attributes'),
            ],
        ]);
        $fieldsetCasAttributes = $this->get('fieldset_cas_attributes');        
        
        $fieldsetCasAttributes->add([
            'type' => 'Text',
            'name' => 'cas_user_email_attribute',
            'options' => [
                'label' => 'User email attribute', // @translate
                'info' => 'If set, this attribute will be used as the user email when creating a new Omeka S user account', // @translate
            ],
            'attributes' => [
                'id' => 'cas_user_email_attribute',
                'required' => true,
                'placeholder' => 'mail',
            ],
        ]);
        $fieldsetCasAttributes->add([
            'type' => 'Text',
            'name' => 'cas_user_name_attribute',
            'options' => [
                'label' => 'User name attribute', // @translate
                'info' => 'If set, this attribute will be used as the user name when creating a new Omeka S user account', // @translate
            ],
            'attributes' => [
                'id' => 'cas_user_email_attribute',
                'required' => false,
                'placeholder' => 'uid',
            ],
        ]);

        // User accounts
        $this->add([
            'type' => Fieldset::class,
            'name' => 'fieldset_user_accounts',
            'options' => [
                'label' => $this->translate('User accounts'),
            ],
        ]);
        $fieldsetUserAccounts = $this->get('fieldset_user_accounts');

        $fieldsetUserAccounts->add([
            'name' => 'cas_accounts_auto_register',
            'type' => Element\Checkbox::class,
            'options' => [
                'label' => $this->translate("Automatically create accounts"),
                'info' => $this->translate("If checked, a user account is automatically created the first time a user logs into the site. If disabled, you will need to pre-register accounts."),
            ],
            'attributes' => [
                'id' => 'cas_users_register',
            ],
        ]);

        $roles = $this->getAcl()->getRoleLabels(true);
        $fieldsetUserAccounts->add([
            'name' => 'cas_accounts_default_role',
            'type' => 'select',
            'options' => [
                'label' => 'Default role', // @translate
                'empty_option' => 'Select role…', // @translate
                'value_options' => $roles,
            ],
            'attributes' => [
                'id' => 'cas_accounts_default_role',
                'required' => false,
            ],
        ]);

    }

    /**
     * @param Acl $acl
     */
    public function setAcl(Acl $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @return Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param $args
     * @return string
     */
    protected function translate($args)
    {
        $translator = $this->getTranslator();
        return $translator->translate($args);
    }
}