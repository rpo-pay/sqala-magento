<?php

namespace Sqala\Payment\Gateway\Http\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Sqala\Payment\Gateway\Config\Config;

class SqalaService
{
    public const AUTH_URL = 'https://api.sqala.tech/core/v1/access-tokens';
    public const PIX_URL = 'https://api.sqala.tech/core/v1/pix-qrcode-payments';

    private string $authorizationToken;
    private string $accessToken;

    public function __construct(private readonly Config $config)
    {
        $this->authorizationToken = $this->getAuthorizationToken();
        $this->accessToken = $this->createAccessToken();
    }


    public function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function createPix(array $data): array
    {
        $client = new Client();
        try {
            $response = $client->request(
                'POST',
                self::PIX_URL,
                [
                    'headers' => $this->getHeaders(),
                    'body' => json_encode($data),
                ]
            );
            return json_decode((string)$response->getBody(), true);
        } catch (ClientException $exception) {
            return json_decode((string)$exception->getResponse()->getBody(), true);
        }
    }

    public function getPix(string $id): array
    {
        $client = new Client();
        $response = $client->request(
            'GET',
            self::PIX_URL.'/'.$id,
            [
                'headers' => $this->getHeaders(),
            ]
        );
        return json_decode((string)$response->getBody(), true);
    }

    public function refundPix(string $id, string $reason): array
    {
        $client = new Client();
        try {
            $response = $client->request(
                'DELETE',
                self::PIX_URL.'/'.$id.'/refund',
                [
                    'headers' => $this->getHeaders(),
                    'body' => json_encode([
                        'reason' => $reason,
                    ])
                ]
            );
            return ['status' => $response->getStatusCode()];
        } catch (ClientException $exception) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('====== SqalaService - $response: ', ['url' => self::PIX_URL.'/'.$id.'/refund', 'headers' => $this->getHeaders()]);
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('====== SqalaService - $response: ', json_decode((string)$exception->getResponse()->getBody(), true));

            return [
                ...json_decode((string)$exception->getResponse()->getBody(), true),
                'status' => $exception->getResponse()->getStatusCode()
            ];
        }
    }

    private function createAccessToken(): string
    {
        try {
            $refreshToken = $this->config->getAddtionalValue('refresh_token');
            $client = new Client();
            $response = $client->request(
                'POST',
                self::AUTH_URL,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Basic ' . $this->getAuthorizationToken(),
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode(['refreshToken' => $refreshToken]),
                ]
            );
            $data = json_decode((string)$response->getBody(), true);
            return $data['token'];
        } catch (GuzzleException $e) {
            return '';
        }
    }

    private function getAuthorizationToken(): string
    {
        $appId = $this->config->getAddtionalValue('app_id');
        $appSecret = $this->config->getAddtionalValue('app_secret');
        return base64_encode("$appId:$appSecret");
    }
}
