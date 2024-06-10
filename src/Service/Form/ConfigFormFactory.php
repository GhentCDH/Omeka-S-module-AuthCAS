<?php
namespace AuthCAS\Service\Form;

use AuthCAS\Form\ConfigForm;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConfigFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
    {
        $form = new ConfigForm(null, $options ?? []);
        $form->setTranslator($services->get('MvcTranslator'));
        $form->setAcl($services->get('Omeka\Acl'));

        return $form;
    }
}