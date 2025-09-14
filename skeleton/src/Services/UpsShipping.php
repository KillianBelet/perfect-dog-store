<?php

namespace App\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpsShipping
{
    private string $accessKey;
    private string $username;
    private string $password;
    private string $accountNumber;
    private HttpClientInterface $client;

    public function __construct(
        HttpClientInterface $client,
        string $accessKey,
        string $username,
        string $password,
        string $accountNumber
    ) {
        $this->client = $client;
        $this->accessKey = $accessKey;
        $this->username = $username;
        $this->password = $password;
        $this->accountNumber = $accountNumber;
    }

    public function getRates(array $fromAddress, array $toAddress, float $weightKg): array
    {
        $url = 'https://wwwcie.ups.com/rest/Rate'; // Sandbox UPS

        $payload = [
            "UPSSecurity" => [
                "UsernameToken" => [
                    "Username" => $this->username,
                    "Password" => $this->password
                ],
                "ServiceAccessToken" => [
                    "AccessLicenseNumber" => $this->accessKey
                ]
            ],
            "RateRequest" => [
                "Request" => [
                    "RequestOption" => "Rate"
                ],
                "Shipment" => [
                    "Shipper" => [
                        "Name" => "Perfect Dog Store",
                        "ShipperNumber" => $this->accountNumber,
                        "Address" => $fromAddress
                    ],
                    "ShipTo" => [
                        "Name" => "Client",
                        "Address" => $toAddress
                    ],
                    "Service" => [
                        "Code" => "11", // UPS Standard
                        "Description" => "UPS Standard"
                    ],
                    "Package" => [
                        "PackagingType" => ["Code" => "02"],
                        "PackageWeight" => [
                            "UnitOfMeasurement" => ["Code" => "KGS"],
                            "Weight" => (string) $weightKg
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->request('POST', $url, [
            'json' => $payload
        ]);

        $data = $response->toArray(false);

        return $data['RateResponse']['RatedShipment'] ?? [];
    }
}
