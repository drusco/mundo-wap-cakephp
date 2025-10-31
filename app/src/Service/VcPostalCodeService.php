<?php

namespace App\Service;

use Cake\Http\Client;

/**
 * Service to handle postal code information using Via CEP API.
 */
class VcPostalCodeService implements PostalCodeServiceInterface
{
    private Client $httpClient;
    private string $apiUrl = 'https://viacep.com.br/ws/';
    
    public function fetchPostalCode(string $postalCode): ?array
    {
        return null;
    }
}