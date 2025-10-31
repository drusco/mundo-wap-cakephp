<?php
namespace App\Service\Provider;

use App\Service\Interface\PostalCodeServiceInterface;
use App\Service\RvPostalCodeService;
use Cake\Core\ContainerInterface;
use Cake\Core\ServiceProvider;

class PostalCodeServiceProvider extends ServiceProvider
{
    protected $provides = [
        PostalCodeServiceInterface::class,
        RvPostalCodeService::class
    ];

    public function services(ContainerInterface $container): void 
    {
        $container->add(PostalCodeServiceInterface::class, RvPostalCodeService::class);
    }
}