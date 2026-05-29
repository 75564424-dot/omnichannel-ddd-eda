<?php

declare(strict_types=1);

namespace App\Integration\Interfaces\Providers;

use App\Integration\Application\Services\AdapterPipeline;
use App\Integration\Application\Services\IntegrationCredentialCipher;
use App\Integration\Application\UseCases\CreateChannelUseCase;
use App\Integration\Application\UseCases\CreateIntegrationUseCase;
use App\Integration\Application\UseCases\DeleteChannelUseCase;
use App\Integration\Application\UseCases\DeleteIntegrationUseCase;
use App\Integration\Application\UseCases\DispatchOutboundConnectorUseCase;
use App\Integration\Application\UseCases\GetChannelUseCase;
use App\Integration\Application\UseCases\GetIntegrationUseCase;
use App\Integration\Application\UseCases\ListChannelsUseCase;
use App\Integration\Application\UseCases\ListIntegrationsUseCase;
use App\Integration\Application\UseCases\ReceiveWebhookUseCase;
use App\Integration\Application\UseCases\StoreIntegrationCredentialUseCase;
use App\Integration\Application\UseCases\UpdateChannelUseCase;
use App\Integration\Application\UseCases\UpdateIntegrationUseCase;
use App\Integration\Domain\Contracts\ExternalEventPublisherInterface;
use App\Integration\Domain\Contracts\OutboundConnectorInterface;
use App\Integration\Domain\Repositories\AdapterRepositoryInterface;
use App\Integration\Domain\Repositories\ChannelRepositoryInterface;
use App\Integration\Domain\Repositories\ConnectorRepositoryInterface;
use App\Integration\Domain\Repositories\IntegrationCredentialRepositoryInterface;
use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;
use App\Integration\Domain\Repositories\WebhookRequestRepositoryInterface;
use App\Integration\Domain\Services\WebhookSignatureVerifier;
use App\Integration\Infrastructure\Adapters\AdapterRegistry;
use App\Integration\Infrastructure\Adapters\FieldMapAdapter;
use App\Integration\Infrastructure\Adapters\JsonValidateAdapter;
use App\Integration\Infrastructure\Connectors\HttpOutboundConnector;
use App\Integration\Infrastructure\Middleware\BusExternalEventPublisher;
use App\Integration\Infrastructure\Persistence\EloquentAdapterRepository;
use App\Integration\Infrastructure\Persistence\EloquentChannelRepository;
use App\Integration\Infrastructure\Persistence\EloquentConnectorRepository;
use App\Integration\Infrastructure\Persistence\EloquentIntegrationCredentialRepository;
use App\Integration\Infrastructure\Persistence\EloquentIntegrationRepository;
use App\Integration\Infrastructure\Persistence\EloquentWebhookRequestRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ChannelRepositoryInterface::class, EloquentChannelRepository::class);
        $this->app->bind(IntegrationRepositoryInterface::class, EloquentIntegrationRepository::class);
        $this->app->bind(IntegrationCredentialRepositoryInterface::class, EloquentIntegrationCredentialRepository::class);
        $this->app->bind(WebhookRequestRepositoryInterface::class, EloquentWebhookRequestRepository::class);
        $this->app->bind(AdapterRepositoryInterface::class, EloquentAdapterRepository::class);
        $this->app->bind(ConnectorRepositoryInterface::class, EloquentConnectorRepository::class);
        $this->app->bind(OutboundConnectorInterface::class, HttpOutboundConnector::class);
        $this->app->bind(ExternalEventPublisherInterface::class, BusExternalEventPublisher::class);

        $this->app->singleton(WebhookSignatureVerifier::class);
        $this->app->singleton(IntegrationCredentialCipher::class);
        $this->app->singleton(AdapterPipeline::class);

        $this->app->singleton(AdapterRegistry::class, function () {
            $registry = new AdapterRegistry;
            $registry->register(new JsonValidateAdapter);
            $registry->register(new FieldMapAdapter);

            return $registry;
        });

        $this->app->bind(ReceiveWebhookUseCase::class);
        $this->app->bind(ListChannelsUseCase::class);
        $this->app->bind(GetChannelUseCase::class);
        $this->app->bind(CreateChannelUseCase::class);
        $this->app->bind(UpdateChannelUseCase::class);
        $this->app->bind(DeleteChannelUseCase::class);
        $this->app->bind(ListIntegrationsUseCase::class);
        $this->app->bind(GetIntegrationUseCase::class);
        $this->app->bind(CreateIntegrationUseCase::class);
        $this->app->bind(UpdateIntegrationUseCase::class);
        $this->app->bind(DeleteIntegrationUseCase::class);
        $this->app->bind(StoreIntegrationCredentialUseCase::class);
        $this->app->bind(DispatchOutboundConnectorUseCase::class);
    }

    public function boot(): void
    {
        Route::middleware('api')->group(
            __DIR__.'/../Routes/api.php'
        );
    }
}
