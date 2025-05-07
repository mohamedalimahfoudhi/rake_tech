<?php

namespace App\Bundle\TwilioSmsBundle\Services;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;

class TwilioSmsService
{
    private $twilioClient;
    private $twilioNumber;
    private $logger;

    public function __construct(
        string $accountSid, 
        string $authToken, 
        string $twilioNumber,
        LoggerInterface $logger
    ) {
        $this->twilioNumber = $twilioNumber;
        $this->logger = $logger;
        
        $this->twilioClient = new Client($accountSid, $authToken);
    }

    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        try {
            // Format Tunisian phone number
            $phoneNumber = $this->formatTunisianNumber($phoneNumber);
            
            $this->logger->info('Attempting to send SMS to: ' . $phoneNumber);
            
            $this->twilioClient->messages->create(
                $phoneNumber,
                [
                    'from' => $this->twilioNumber,
                    'body' => "Your verification code is: $code"
                ]
            );
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Twilio SMS Error: ' . $e->getMessage(), [
                'phoneNumber' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    public function sendSms(string $phoneNumber, string $message): bool 
    {
        try {
            // Format Tunisian phone number
            $phoneNumber = $this->formatTunisianNumber($phoneNumber);
            
            $this->logger->info('Attempting to send SMS to: ' . $phoneNumber);
            
            $this->twilioClient->messages->create(
                $phoneNumber,
                [
                    'from' => $this->twilioNumber,
                    'body' => $message
                ]
            );
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Twilio SMS Error: ' . $e->getMessage(), [
                'phoneNumber' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function formatTunisianNumber(string $phoneNumber): string
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If number starts with 0, remove it
        if (strpos($phoneNumber, '0') === 0) {
            $phoneNumber = substr($phoneNumber, 1);
        }
        
        // If number doesn't start with 216, add it
        if (strpos($phoneNumber, '216') !== 0) {
            $phoneNumber = '216' . $phoneNumber;
        }
        
        // Add the + prefix
        return '+' . $phoneNumber;
    }
} 