<?php
namespace App\Service;

use Cake\Http\Client;
use App\Service\Interface\PostalCodeServiceInterface;

/**
 * Service to handle postal code information using Republica Virtual API.
 */
class RvPostalCodeService implements PostalCodeServiceInterface
{
    private Client $httpClient;
    private string $apiUrl = 'http://cep.republicavirtual.com.br/web_cep.php';
    
    public function fetchPostalCode(string $postalCode): ?array
    {
        return null;
    }
}