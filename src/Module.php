<?php


namespace Carnage\WatsonZf2;

use Carnage\Watson\Logger\LoggerInterface;
use Carnage\Watson\Logger\SimpleLogger;
use Carnage\WatsonZf2\Service\ConfigurationDelegatorFactory;
use Zend\Log\Logger;
use Zend\Log\PsrLoggerAdapter;
use Zend\Log\Writer\Stream;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;

class Module implements
    ConfigProviderInterface,
    InitProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init(ModuleManagerInterface $manager)
    {
        $events = $manager->getEventManager();
        // Initialize logger collector once the profiler is initialized itself
        $events->attach(
            MvcEvent::EVENT_FINISH,
            function (\Zend\Mvc\MvcEvent $e) {
                $sm = $e->getApplication()->getServiceManager();
                $logger = $sm->get(LoggerInterface::class);
                $queries = $logger->getQueries();
                /** @var \Psr\Log\LoggerInterface $logger */
                $logger = $sm->get('WatsonZendLog');

                foreach ($queries as $query) {
                    $logger->debug(json_encode($query));
                }
            }
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return [
            'service_manager' => [
                'delegators' => [
                    'doctrine.configuration.orm_default' => [
                        ConfigurationDelegatorFactory::class,
                    ]
                ],
                'invokables' => [
                    LoggerInterface::class => SimpleLogger::class
                ],
                'factories' => [
                    'WatsonZendLog' => function ($sm) {
                        $filename = 'watson_query_log_' . uniqid() . '.log';
                        $log = new Logger();
                        $writer = new Stream('./data/logs/' . $filename);
                        $log->addWriter($writer);

                        return new PsrLoggerAdapter($log);
                    },
                ],
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleDependencies()
    {
        return ['DoctrineOrmModule'];
    }
}
