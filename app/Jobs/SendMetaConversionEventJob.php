<?php

namespace App\Jobs;

use App\Models\Niche;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendMetaConversionEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $eventData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $productId = $this->eventData['product_id'] ?? null;
        $nicheId = $this->eventData['niche_id'] ?? null;

        $niche = null;
        $product = null;

        if ($nicheId) {
            $niche = Niche::find($nicheId);
        }

        if ($productId) {
            $product = Product::with(['store', 'niche'])->find($productId);
            if ($product && !$niche) {
                $niche = $product->niche;
            }
        }

        // Resolvendo credenciais dinâmicas do nicho ou aplicando o fallback global
        $pixelId = !empty($niche?->meta_pixel_id) ? $niche->meta_pixel_id : config('services.meta.pixel_id');
        $accessToken = !empty($niche?->meta_access_token) ? $niche->meta_access_token : config('services.meta.access_token');

        if (empty($pixelId) || empty($accessToken)) {
            Log::debug('Meta CAPI: Integração não configurada (chaves ausentes).');
            return;
        }

        $eventType = $this->eventData['event_type'] ?? 'page_view';
        $metaEventName = $this->mapEventName($eventType);

        // Estruturar dados do usuário
        $userData = [
            'client_ip_address' => $this->eventData['client_ip'] ?? null,
            'client_user_agent' => $this->eventData['user_agent'] ?? null,
        ];

        if (!empty($this->eventData['fbp'])) {
            $userData['fbp'] = $this->eventData['fbp'];
        }
        if (!empty($this->eventData['fbc'])) {
            $userData['fbc'] = $this->eventData['fbc'];
        }

        // Se houver usuário logado, adicione e-mail com hash SHA-256
        if (!empty($this->eventData['user_id'])) {
            $user = User::find($this->eventData['user_id']);
            if ($user && !empty($user->email)) {
                $hashedEmail = hash('sha256', strtolower(trim($user->email)));
                $userData['em'] = [$hashedEmail];
            }
        }

        // Remover campos nulos do user_data
        $userData = array_filter($userData, fn($v) => !is_null($v));

        // Estruturar dados customizados (detalhes do produto/nicho)
        $customData = [];

        if ($product) {
            $customData = [
                'content_name'     => $product->name,
                'content_ids'      => [$product->id],
                'content_type'     => 'product',
                'content_category' => $product->niche?->name ?? 'Geral',
            ];

            if (!is_null($product->price)) {
                $customData['value'] = (float) $product->price;
                $customData['currency'] = 'BRL';
            }
        } elseif ($nicheId) {
            $niche = $niche ?? Niche::find($nicheId);
            if ($niche) {
                $customData = [
                    'content_name'     => $niche->name,
                    'content_category' => $niche->name,
                    'content_type'     => 'product_group',
                ];
            }
        }

        // Estruturar o payload do evento
        $eventPayload = [
            'event_name'       => $metaEventName,
            'event_time'       => time(),
            'event_id'         => $this->eventData['event_id'] ?? null,
            'event_source_url' => $this->eventData['event_source_url'] ?? null,
            'action_source'    => 'website',
            'user_data'        => $userData,
        ];

        if (!empty($customData)) {
            $eventPayload['custom_data'] = $customData;
        }

        // Limpar chaves nulas do payload
        $eventPayload = array_filter($eventPayload, fn($v) => !is_null($v));

        // Enviar requisição para a API de Conversões da Meta
        $url = "https://graph.facebook.com/v19.0/{$pixelId}/events";

        try {
            $response = Http::post($url, [
                'data'         => [$eventPayload],
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                Log::warning('Meta CAPI: Erro ao enviar evento de conversão.', [
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                    'payload'  => $eventPayload,
                ]);
            } else {
                Log::debug('Meta CAPI: Evento enviado com sucesso.', [
                    'event_name' => $metaEventName,
                    'event_id'   => $this->eventData['event_id'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Meta CAPI: Exceção ao tentar enviar evento.', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Mapeia tipos de eventos internos para nomes padrão da Meta.
     */
    protected function mapEventName(string $eventType): string
    {
        return match ($eventType) {
            'page_view'          => 'PageView',
            'niche_view'         => 'ViewContent',
            'product_view'       => 'ViewContent',
            'product_click'      => 'InitiateCheckout',
            default              => 'PageView',
        };
    }
}
