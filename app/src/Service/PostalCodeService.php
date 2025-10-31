<?php
namespace App\Service;

use App\Service\Interface\PostalCodeServiceInterface;

/**
 * Service to handle postal code using fallback logic.
 */
class PostalCodeService implements PostalCodeServiceInterface
{
    public function fetchPostalCode(string $postalCode): ?array
    {
        return null;
    }
}