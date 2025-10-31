<?php
namespace \App\Service;

interface PostalCodeServiceInterface
{
    /**
     * Fetch postal code information.
     * 
     * @param string $postalCode The postal code to fetch
     * @return array|null Returns postal code data or null if not found
     */
    public function fetchPostalCode(string $postalCode): ?array;
}