<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    private array $pendingImages = [];
    private array $pendingVideos = [];

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $media = $this->record->media()->get();

        $data['gallery_images'] = $media
            ->where('type', 'image')
            ->sortBy('sort_order')
            ->pluck('path')
            ->filter()
            ->values()
            ->toArray();

        $data['gallery_videos'] = $media
            ->where('type', 'video')
            ->sortBy('sort_order')
            ->map(fn ($m) => ['video_url' => $m->video_url])
            ->values()
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingImages = array_values(array_filter((array) ($data['gallery_images'] ?? [])));
        $this->pendingVideos = array_values(array_filter((array) ($data['gallery_videos'] ?? [])));

        unset($data['gallery_images'], $data['gallery_videos']);

        if (!empty($this->pendingImages[0])) {
            $data['image_path'] = $this->pendingImages[0];
        }

        return $data;
    }

    protected function afterSave(): void
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
