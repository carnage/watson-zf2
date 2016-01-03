<?php

namespace Carnage\WatsonZf2\Service;

use Carnage\Watson\Logger\LoggerInterface;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Carnage\Watson\Configuration;

class ConfigurationDelegatorFactory implements DelegatorFactoryInterface
{
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        /** @var LoggerInterface $logger */
        $logger = $serviceLocator->get(LoggerInterface::class);
        $configuration = new Configuration($callback());
        $configuration->setWatsonLogger($logger);

        return $configuration;
    }
}