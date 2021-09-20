<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Excel;

abstract class Base implements FromCollection, Responsable, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * Optional Writer Type
     */
    protected $writerType = Excel::CSV;

    /**
     * Optional headers
     */
    protected $headers = [
        'Content-Type' => 'text/csv',
        'Cache-Control'=> 'no-cache, public, must-revalidate, proxy-revalidate',
        'Expires' =>  0,
    ];

    /**
     * Definition of heading and corresponding column name
     * Attributes need to be overwritten
     */
    protected $fields = [];

    /**
     * Definition of filename
     * Attributes need to be overwritten
     */
    protected $fileName = '';

    /**
     * @var Illuminate\Support\Collection $data
     */
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return array_values($this->fields);
    }

    /**
     * @return Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * Convert model to array
     * @param mixed $model
     * @return array
     */
    public function map($model): array
    {
        $arr = [];
        foreach ($this->fields as $key => $val) {
            $arr[] = $this->transform($model, $key, $model->$key);
        }

        return $arr;
    }

    /**
     * Transform value by key or other attribute of model
     * @param mixed $model mixed model
     * @param string $key attribute key
     * @param mixed $val attribute value
     * @return string
     */
    protected function transform(mixed $model, String $key, mixed $val): String
    {
        return (string) $val;
    }
}
