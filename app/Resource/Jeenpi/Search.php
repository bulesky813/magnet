<?php

namespace App\Resource\Jeenpi;

use Hyperf\Resource\Json\JsonResource;
use Hyperf\Utils\Arr;

class Search extends JsonResource
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
            "Data" => collect($this->resource)->slice(0, 10)
        ];
    }
}
