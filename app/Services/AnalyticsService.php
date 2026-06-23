<?php

namespace App\Services;

use App\DTOs\EventDTO;
use App\Repositories\AnalyticsRepository;

class AnalyticsService
{
    public function __construct(
        private readonly AnalyticsRepository $analyticsRepository,
    ) {}

    public function record(EventDTO $dto): void
    {
        $this->analyticsRepository->record([
            'session_id'   => $dto->sessionId,
            'user_id'      => $dto->userId,
            'event_type'   => $dto->eventType,
            'niche_id'     => $dto->nicheId,
            'product_id'   => $dto->productId,
            'store_id'     => $dto->storeId,
            'utm_source'   => $dto->utmSource,
            'utm_medium'   => $dto->utmMedium,
            'utm_campaign' => $dto->utmCampaign,
            'utm_content'  => $dto->utmContent,
            'referrer'     => $dto->referrer,
            'user_agent'   => $dto->userAgent,
            'metadata'     => $dto->metadata,
        ]);

        if (config('services.meta.pixel_id') && config('services.meta.access_token')) {
            \App\Jobs\SendMetaConversionEventJob::dispatch([
                'event_type'       => $dto->eventType,
                'session_id'       => $dto->sessionId,
                'user_id'          => $dto->userId,
                'niche_id'         => $dto->nicheId,
                'product_id'       => $dto->productId,
                'store_id'         => $dto->storeId,
                'event_id'         => $dto->eventId,
                'event_source_url' => $dto->eventSourceUrl,
                'client_ip'        => $dto->clientIp,
                'user_agent'       => $dto->userAgent,
                'fbp'              => $dto->fbp,
                'fbc'              => $dto->fbc,
            ]);
        }
    }

    public function claimSession(string $sessionId, string $userId): void
    {
        $this->analyticsRepository->claimSession($sessionId, $userId);
    }
}
