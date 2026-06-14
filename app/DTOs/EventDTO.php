<?php

namespace App\DTOs;

class EventDTO
{
    public function __construct(
        public readonly string  $sessionId,
        public readonly string  $eventType,
        public readonly ?string $userId      = null,
        public readonly ?string $nicheId     = null,
        public readonly ?string $productId   = null,
        public readonly ?string $storeId     = null,
        public readonly ?string $utmSource   = null,
        public readonly ?string $utmMedium   = null,
        public readonly ?string $utmCampaign = null,
        public readonly ?string $utmContent  = null,
        public readonly ?string $referrer    = null,
        public readonly ?string $userAgent   = null,
        public readonly ?array  $metadata    = null,
    ) {}
}
