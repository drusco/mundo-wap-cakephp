<?php
namespace App\Service;

use App\Service\Interface\PostalCodeServiceInterface;

/**
 * Service to handle postal code using primary and fallback services.
 */
class PostalCodeService implements PostalCodeServiceInterface
{
    public function __construct(
        private PostalCodeServiceInterface $primaryService,
        private PostalCodeServiceInterface $fallbackService,
    ){}

    public function fetchPostalCode(string $postalCode): ?array
    {
        // call the primary service first
        $result = $this->primaryService->fetchPostalCode($postalCode);
        
        if ($result === null) {
            // call the fallback service if primary fails
            $result = $this->fallbackService->fetchPostalCode($postalCode);
        }

        // return the final result
        return $result;
    }
}