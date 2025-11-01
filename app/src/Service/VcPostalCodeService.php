<?php
namespace App\Service;

use Cake\Http\Client;
use App\Service\Interface\PostalCodeServiceInterface;

/**
 * Service to handle postal code information using Via CEP API.
 */
class VcPostalCodeService implements PostalCodeServiceInterface
{
    private string $apiUrl = 'https://viacep.com.br/ws/';

    public function __construct(
        private Client $http,
    ){}
    
    public function fetchPostalCode(string $postalCode): ?array
    {
        // call the API with the given postal code
        $response = $this->http->get("{$this->apiUrl}{$postalCode}/json");

        if($response->isOk()) {
            // parse the json response
            $data = $response->getJson();
            if ($data && !isset($data['erro'])) {
                return [
                    'postal_code' => $postalCode,
                    'sublocality' => $data['bairro'],
                    'street' => $data['logradouro'],
                    'complement' => $data['complemento'],
                    'city' => $data['localidade'],
                    'state' => $data['uf'],
                ];
            }
        }

        return null;
    }
}