<?php

namespace App\Bundle\TwilioSmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('twilio_sms');
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->children()
                ->scalarNode('account_sid')
                    ->isRequired()
                    ->info('Twilio Account SID')
                ->end()
                ->scalarNode('auth_token')
                    ->isRequired()
                    ->info('Twilio Auth Token')
                ->end()
                ->scalarNode('phone_number')
                    ->isRequired()
                    ->info('Twilio Phone Number')
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
} 