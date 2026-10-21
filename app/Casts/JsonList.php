<?php

namespace App\Casts;

use App\Support\JsonList as Decoder;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class JsonList implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return Decoder::decode($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode(Decoder::decode($value), JSON_UNESCAPED_UNICODE);
    }
}
