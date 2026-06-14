<?php

namespace App\Http\Controllers;

use App\DTOs\EventDTO;
use App\Http\Requests\EventRequest;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function store(EventRequest $request): JsonResponse
    {
        $user = $request->user('api');

        $this->analyticsService->record(new EventDTO(
            sessionId:   $request->session_id,
            eventType:   $request->event_type,
            userId:      $user?->id,
            nicheId:     $request->niche_id,
            productId:   $request->product_id,
            storeId:     $request->store_id,
            utmSource:   $request->utm_source,
            utmMedium:   $request->utm_medium,
            utmCampaign: $request->utm_campaign,
            utmContent:  $request->utm_content,
            referrer:    $request->header('Referer'),
            userAgent:   $request->userAgent(),
            metadata:    $request->metadata,
        ));

        return response()->json(['success' => true, 'message' => 'OK'], 201);
    }
}
