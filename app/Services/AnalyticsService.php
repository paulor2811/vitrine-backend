<?php

namespace App\Services;

use App\DTOs\EventDTO;
use App\Repositories\AnalyticsRepository;

class AnalyticsService
{
    public function __construct(
        private readonly AnalyticsRepository $analyticsRepository,
    ) {}

    // Eventos que valem ser persistidos internamente.
    // page_view não tem contexto (sem nicho/produto) e só serve para o Meta CAPI.
    private const PERSISTED_EVENTS = ['niche_view', 'product_view', 'product_click'];

    public function record(EventDTO $dto): void
    {
        if (in_array($dto->eventType, self::PERSISTED_EVENTS, strict: true)) {
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
        }

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

    // Registra redirecionamento server-side sem disparar Meta CAPI,
    // pois o evento browser-side (product_click) já cuida disso.
    public function recordRedirect(string $productId, ?string $nicheId, ?string $storeId, \Illuminate\Http\Request $request): void
    {
        $this->analyticsRepository->record([
            'session_id'   => \Illuminate\Support\Str::uuid()->toString(),
            'event_type'   => 'product_redirect',
            'product_id'   => $productId,
            'niche_id'     => $nicheId,
            'store_id'     => $storeId,
            'utm_source'   => $request->query('utm_source'),
            'utm_medium'   => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_content'  => $request->query('utm_content'),
            'referrer'     => $request->header('Referer'),
            'user_agent'   => $request->userAgent(),
        ]);
    }
}
