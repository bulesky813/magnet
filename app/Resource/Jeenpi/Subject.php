<?php

namespace App\Resource\Jeenpi;

use Hyperf\Resource\Json\JsonResource;
use Hyperf\Utils\Arr;

class Subject extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ProxyPort' => '',
            "Msg" => "",
            "ActiveProxy" => false,
            "ProxyIP" => "",
            "Data" => [
                'genres' => Arr::get($this->resource, 'genres', []),
                'alt' => Arr::get($this->resource, 'alt', ''),
                'images_medium' => Arr::get($this->resource, 'images_medium', ''),
                'directors' => [],
                'images_large' => Arr::get($this->resource, 'images_large', ''),
                'original_title' => Arr::get($this->resource, 'original_title', []),
                'id' => Arr::get($this->resource, 'alt', ''),
                'summary' => Arr::get($this->resource, 'summary', ''),
                'title' => Arr::get($this->resource, 'title', ''),
                'countries' => [
                    '日本'
                ],
                'rating' => Arr::get($this->resource, 'rating', ''),
                'year' => Arr::get($this->resource, 'year', ''),
                'casts' => Arr::get($this->resource, 'casts', [])
            ]
        ];
    }
}
