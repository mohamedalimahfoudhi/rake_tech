<?php

namespace App\Bundle\TwilioSmsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TwilioSmsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__).'/TwilioSmsBundle';
    }
} 