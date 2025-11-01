<?php
namespace App\Service\Provider;

use App\Service\Interface\PostalCodeServiceInterface;
use App\Service\PostalCodeService;
use App\Service\RvPostalCodeService;
use App\Service\VcPostalCodeService;
use Cake\Core\ContainerInterface;
use Cake\Core\ServiceProvider;

class PostalCodeServiceProvider extends ServiceProvider
{
    protected $provides = [
        PostalCodeServiceInterface::class,
        RvPostalCodeService::class,
        VcPostalCodeService::class,
        PostalCodeService::class,
    ];

    public function services(ContainerInterface $container): void 
    {
        // Add the primary and secondary postal code services
        $container->addShared(RvPostalCodeService::class);
        $container->addShared(VcPostalCodeService::class);

        // Add the postal code service interface and its implementation
        $container->addShared(PostalCodeServiceInterface::class, PostalCodeService::class)
            ->addArgument( RvPostalCodeService::class)
            ->addArgument( VcPostalCodeService::class);
       
    }
}