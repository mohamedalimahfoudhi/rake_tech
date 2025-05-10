<?php

namespace App;

use App\Bundle\TwilioSmsBundle\TwilioSmsBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    
    /**
     * Register bundles
     *
     * @return iterable<BundleInterface>
     */
    public function registerBundles(): iterable
    {
        // Instead of calling parent method which is abstract,
        // create a new array for bundles
        $bundles = [];
        
        // Get bundles from config/bundles.php
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                $bundles[] = new $class();
            }
        }
        
        // Register your custom bundle
        $bundles[] = new TwilioSmsBundle();
        
        return $bundles;
    }
}
