<?php

namespace App\Transformers\Serializer;

use League\Fractal\Serializer\DataArraySerializer as Base;
use League\Fractal\Pagination\PaginatorInterface;
use Illuminate\Support\Str;

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

    public function paginator(PaginatorInterface $paginator)
    {
        $pagination = parent::paginator($paginator);
        $sort = request()->get('sort', 'id');
        $pagination['pagination']['sortBy'] = str_replace('-', '', $sort);
        $pagination['pagination']['descending'] = Str::contains($sort, '-');
        return $pagination;
    }
}
