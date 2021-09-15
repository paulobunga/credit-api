<?php

namespace App\Exports;

class BankExport extends Base
{
    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    protected $fileName = 'banks.xlsx';

    protected $fields = [
        'id' => 'Bank id',
        'ident' => 'Bank ident',
        'name' => 'Bank name',
        'status' => 'status',

    ];

    public function map($bank): array
    {
        return collect(array_keys($this->fields))->map(function ($v) use ($bank) {
            switch ($v) {
                case 'status':
                    return $bank->$v ? 'Active' : 'Inactive';
                default:
                    return $bank->$v;
            }
        })->toArray();
    }
}
