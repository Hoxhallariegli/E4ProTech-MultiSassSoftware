<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $content = $this->content;
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $content = $decoded;
            } else {
                $content = ['sq' => $content, 'en' => $content];
            }
        }

        return [
            'id' => $this->id,
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'type' => $this->type,
            'content' => $content,
            'content_sq' => is_array($content) ? ($content['sq'] ?? '') : (string) $content,
            'content_en' => is_array($content) ? ($content['en'] ?? '') : (string) $content,
            'barberShop' => $this->whenLoaded('barberShop'),
        ];
    }
}
