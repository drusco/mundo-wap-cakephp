<?php
namespace App\Service;

use Cake\Http\Client;
use App\Service\Interface\PostalCodeServiceInterface;

/**
 * Service to handle postal code information using Republica Virtual API.
 */
class RvPostalCodeService implements PostalCodeServiceInterface
{
    private string $apiUrl = 'http://cep.republicavirtual.com.br/web_cep.php';

    public function __construct(
        private Client $http,
    ){}
    
    public function fetchPostalCode(string $postalCode): ?array
    {
        // call the API with the given postal code
        $response = $this->http->get($this->apiUrl, [
            'cep' => $postalCode,
            'formato' => 'json',
        ]);

        if($response->isOk()) {
            // parse the json response
            $data = $response->getJson();
            if ($data && isset($data['resultado']) && $data['resultado'] != 0) {
                return [
                    'postal_code' => $postalCode,
                    'sublocality' => $data['bairro'],
                    'street' => $data['tipo_logradouro'] . ' ' . $data['logradouro'],
                    'city' => $data['cidade'],
                    'state' => $data['uf'],
                ];
            }
        }

        return null;
    }
}