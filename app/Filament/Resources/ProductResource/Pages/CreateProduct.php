<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    private array $pendingImages = [];
    private array $pendingVideos = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingImages = array_values(array_filter((array) ($data['gallery_images'] ?? [])));
        $this->pendingVideos = array_values(array_filter((array) ($data['gallery_videos'] ?? [])));

        unset($data['gallery_images'], $data['gallery_videos']);

        // Pré-preenche image_path com a primeira imagem para o thumbnail nos cards
        if (!empty($this->pendingImages[0])) {
            $data['image_path'] = $this->pendingImages[0];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncMedia($this->pendingImages, $this->pendingVideos);
    }

    private function syncMedia(array $images, array $videos): void
    {
        $this->record->media()->delete();

        $order = 0;

        foreach ($images as $path) {
            $this->record->media()->create([
                'type'       => 'image',
                'path'       => $path,
                'sort_order' => $order++,
            ]);
        }

        foreach ($videos as $item) {
            $url = $item['video_url'] ?? null;
            if (!$url) continue;
            $this->record->media()->create([
                'type'      => 'video',
                'video_url' => $url,
                'sort_order' => $order++,
            ]);
        }
    }
}
