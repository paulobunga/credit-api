<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Excel;

abstract class BaseExport implements FromCollection, Responsable, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Optional Writer Type
     */
    protected $writerType = Excel::XLSX;

    /**
     * Optional headers
     */
    protected $headers = [
        'Content-Type' => 'text/csv',
    ];

    // attributes need to be overwritten

    protected $fields = [];

    protected $fileName = '';

    public function headings(): array
    {
        return collect(array_values($this->fields))->toArray();
    }

    public function map($model): array
    {
        return collect(array_keys($this->fields))->map(function ($v) use ($model) {
            return $model->$v;
        })->toArray();
    }
}
