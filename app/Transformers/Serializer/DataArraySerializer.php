<?php

namespace App\Transformers\Serializer;

use League\Fractal\Serializer\DataArraySerializer as Base;

class DataArraySerializer extends Base
{
    public function collection($resourceKey, array $data)
    {
        return $resourceKey === false ? $data : parent::collection($resourceKey, $data);
    }

    public function item($resourceKey, array $data)
    {
        return $resourceKey === false ? $data : parent::item($resourceKey, $data);
    }
}
