<?php

namespace thgs\Bootloader;

use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler;
use Psr\Log\LoggerInterface;
use thgs\Bootloader\Config\Configuration;
use thgs\Bootloader\DependencyInjection\Injector;
use function Amp\trapSignal;

final class Boot
{
    /** @var string */
    public const LOG_CONTEXT = 'boot';

    private Bootloader $bootloader;
    private HttpServer $httpServer;
    private ?LoggerInterface $logger;

    public function __construct(
        public readonly Configuration $configuration,
        // can we move these two out and they come from config?
        private Injector $injector,
        private ?RequestHandler $requestHandler = null,
        private readonly ErrorHandler $errorHandler = new DefaultErrorHandler()
    ) {
        $this->boot($this->configuration);
    }

    private function boot(Configuration $configuration): void
    {
        $initTime = hrtime(true);

        $this->bootloader = new Bootloader($this->configuration->logging);
        $this->logger = $this->bootloader->logger;
        $this->httpServer = $this->bootloader->loadServerConfig($configuration->server);

        if ($this->requestHandler instanceof RequestHandler) {
            $this->logger->info('Using override request handler', [self::LOG_CONTEXT]);
        } else {
            $this->requestHandler = $this->bootloader->loadHandler(
                $configuration->requestHandler,
                $this->httpServer,
                $this->logger,
                $this->errorHandler,
                $this->injector,
                $cacheSize = null           // todo: add this
            );
        }

        $someBootTime = (hrtime(true) - $initTime) / 1_000_000_000;
        $this->logger->info("Booted at some $someBootTime seconds");
        $this->httpServer->start($this->requestHandler, $this->errorHandler);

        // todo: this needs better handling
        $signal = trapSignal([\SIGINT, \SIGTERM]);
        $this->stop(sprintf("Received signal %s, stopping HTTP server", $signal === 2 ? 'SIGINT' : 'SIGTERM'));
    }

    public function stop(string $reason): void
    {
        $this->logger?->info($reason);
        $this->httpServer->stop();
    }
}