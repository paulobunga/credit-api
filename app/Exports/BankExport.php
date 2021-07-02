<?php

namespace App\Exports;

use App\Models\Bank;

class BankExport extends BaseExport
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
                    return $bank->$v ? 'Y' : 'N';
                default:
                    return $bank->$v;
            }
        })->toArray();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Bank::select('banks.*')
            // ->sort(request()->get('sort'))
            // ->filter(Request::only('search', 'trashed', 'expired', 'status'))
            ->get();
    }
}
