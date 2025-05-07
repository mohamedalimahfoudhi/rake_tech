<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaService
{
    private $httpClient;
    private $secretKey;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->secretKey = $params->get('recaptcha_secret_key');
    }

    public function verifyToken(string $token, string $action): bool
    {
        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->secretKey,
                    'response' => $token,
                ],
            ]);

            $data = json_decode($response->getContent(), true);

            return $data['success'] && $data['action'] === $action && $data['score'] >= 0.5;
        } catch (\Exception $e) {
            return false;
        }
    }
}