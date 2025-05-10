<?php

namespace App\Bundle\TwilioSmsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class TwilioSmsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        
        $loader->load('services.yaml');
        
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        
        // Set service definition arguments
        $definition = $container->getDefinition('app.twilio_sms.service');
        $definition->setArgument(0, $config['account_sid']);
        $definition->setArgument(1, $config['auth_token']);
        $definition->setArgument(2, $config['phone_number']);
        
        // Set parameters for backward compatibility
        $container->setParameter('twilio_sms.account_sid', $config['account_sid']);
        $container->setParameter('twilio_sms.auth_token', $config['auth_token']);
        $container->setParameter('twilio_sms.phone_number', $config['phone_number']);
        
        // Legacy parameter names for backward compatibility
        $container->setParameter('twilio_account_sid', $config['account_sid']);
        $container->setParameter('twilio_auth_token', $config['auth_token']);
        $container->setParameter('twilio_phone_number', $config['phone_number']);
    }
    
    public function getAlias(): string
    {
        return 'twilio_sms';
    }
} 