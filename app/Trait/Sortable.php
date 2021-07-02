<?php

namespace App\Trait;

use Illuminate\Support\Str;

trait Sortable
{

    public function scopeSort($query, $sorts)
    {
        $fields = $this->getSortableFields();
        $query->when($sorts, function ($query, $sorts) use ($fields) {
            foreach (explode(',', $sorts) as $sort) {
                $sortName = $fields[str_replace('-', '', $sort)];
                $descending = Str::contains($sort, '-');
                if ($descending) {
                    $query->orderByDesc($sortName);
                } else {
                    $query->orderBy($sortName);
                }
            }
        });
    }

    protected function getSortableFields()
    {
        return isset($this->sortable_fields) ? $this->sortable_fields : ['id' => 'id'];
    }
}
