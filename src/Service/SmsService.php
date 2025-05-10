<?php

namespace App\Service;

use App\Bundle\TwilioSmsBundle\Services\TwilioSmsService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Use App\Bundle\TwilioSmsBundle\Services\TwilioSmsService instead
 */
class SmsService extends TwilioSmsService
{
    /**
     * Constructor that maintains backward compatibility with the original implementation
     * but passes the correct parameters to the parent TwilioSmsService
     */
    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $accountSid = $params->get('twilio_account_sid');
        $authToken = $params->get('twilio_auth_token');
        $twilioNumber = $params->get('twilio_phone_number');
        
        // Call parent constructor with the extracted parameters
        parent::__construct($accountSid, $authToken, $twilioNumber, $logger);
    }
} 