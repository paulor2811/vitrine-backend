<?php

namespace Tests\Feature;

use App\Jobs\SendMetaConversionEventJob;
use App\Models\Niche;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_access_token_is_hidden_in_niche_api_responses(): void
    {
        // 1. Criar um nicho com dados de Pixel e Token da Meta
        $niche = Niche::create([
            'name'              => 'Ferramentas de Teste',
            'slug'              => 'ferramentas-teste',
            'description'       => 'Nicho para testar a integração',
            'icon'              => '🛠️',
            'active'            => true,
            'meta_pixel_id'     => '1234567890_pixel',
            'meta_access_token' => 'secret_token_12345',
        ]);

        // 2. Testar rota de listagem (index)
        $responseIndex = $this->getJson(route('niches.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertJsonFragment([
            'slug'          => 'ferramentas-teste',
            'meta_pixel_id' => '1234567890_pixel',
        ]);
        // Garantir que o token não vazou na listagem
        $responseIndex->assertJsonMissing(['meta_access_token' => 'secret_token_12345']);

        // 3. Testar rota de exibição (show)
        $responseShow = $this->getJson(route('niches.show', ['slug' => 'ferramentas-teste']));
        $responseShow->assertStatus(200);
        $responseShow->assertJsonFragment([
            'slug'          => 'ferramentas-teste',
            'meta_pixel_id' => '1234567890_pixel',
        ]);
        // Garantir que o token não vazou na exibição individual
        $responseShow->assertJsonMissing(['meta_access_token' => 'secret_token_12345']);
    }

    public function test_send_meta_conversion_event_job_resolves_niche_credentials(): void
    {
        Http::fake();

        // 1. Criar um nicho com Pixel ID e Access Token específicos
        $niche = Niche::create([
            'name'              => 'Vestuário Teste',
            'slug'              => 'vestuario-teste',
            'description'       => 'Nicho vestuário para teste',
            'icon'              => '👕',
            'active'            => true,
            'meta_pixel_id'     => '999888777_pixel',
            'meta_access_token' => 'niche_access_token_999',
        ]);

        // 2. Disparar o Job associado ao niche_id
        $eventData = [
            'event_type'       => 'niche_view',
            'niche_id'         => $niche->id,
            'event_id'         => 'test-event-uuid-1',
            'event_source_url' => 'http://app.vitrine.localhost/vestuario-teste',
            'client_ip'        => '127.0.0.1',
            'user_agent'       => 'TestAgent',
        ];

        SendMetaConversionEventJob::dispatchSync($eventData);

        // 3. Verificar que o request Http foi feito para a API do Pixel específico com o Token correto
        Http::assertSent(function ($request) use ($niche) {
            return $request->url() === "https://graph.facebook.com/v19.0/{$niche->meta_pixel_id}/events"
                && $request['access_token'] === $niche->meta_access_token
                && $request['data'][0]['event_name'] === 'ViewContent'
                && $request['data'][0]['event_id'] === 'test-event-uuid-1';
        });
    }

    public function test_send_meta_conversion_event_job_resolves_niche_credentials_via_product(): void
    {
        Http::fake();

        // 1. Criar nicho, loja e produto
        $niche = Niche::create([
            'name'              => 'Cozinha Teste',
            'slug'              => 'cozinha-teste',
            'icon'              => '🍳',
            'active'            => true,
            'meta_pixel_id'     => '555666777_pixel',
            'meta_access_token' => 'cozinha_secret_token',
        ]);

        $store = Store::create([
            'name' => 'Loja Teste',
            'slug' => 'loja-teste',
            'active' => true,
        ]);

        $product = Product::create([
            'niche_id'      => $niche->id,
            'store_id'      => $store->id,
            'name'          => 'Frigideira Antiaderente',
            'image_path'    => 'niches/frigideira.jpg',
            'price'         => 99.90,
            'affiliate_url' => 'https://amazon.com.br/frigideira-afiliado',
            'active'        => true,
        ]);

        // 2. Disparar o Job associado ao product_id (nicho será resolvido pelo produto)
        $eventData = [
            'event_type'       => 'product_view',
            'product_id'       => $product->id,
            'event_id'         => 'test-event-uuid-2',
            'event_source_url' => 'http://app.vitrine.localhost/cozinha-teste/' . $product->id,
            'client_ip'        => '127.0.0.1',
            'user_agent'       => 'TestAgent',
        ];

        SendMetaConversionEventJob::dispatchSync($eventData);

        // 3. Verificar que o request Http foi feito para a API com o Pixel e Token do nicho do produto
        Http::assertSent(function ($request) use ($niche, $product) {
            return $request->url() === "https://graph.facebook.com/v19.0/{$niche->meta_pixel_id}/events"
                && $request['access_token'] === $niche->meta_access_token
                && $request['data'][0]['event_name'] === 'ViewContent'
                && $request['data'][0]['event_id'] === 'test-event-uuid-2'
                && $request['data'][0]['custom_data']['content_name'] === $product->name;
        });
    }
}
