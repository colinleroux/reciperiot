<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipePictureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Return recipe picture details
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'title' => $this->title,
            'url' => $this->url,
        ];
    }
}
