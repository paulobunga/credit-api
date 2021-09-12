<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Bank;

class BankImport implements ToModel, ToCollection, WithHeadingRow
{

    public function model(array $row)
    {
        return Bank::firstOrCreate(
            [
                'ident' => $row['ident'],
                'currency' => $row['currency']
            ],
            [
                'name' => $row['name'],
                'status'    => $row['status'] ?? true
            ]
        );
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Bank::firstOrCreate(
                [
                    'ident' => $row['ident'],
                    'currency' => $row['currency']
                ],
                [
                    'name' => $row['name'],
                    'status'    => $row['status'] ?? true
                ]
            );
        }
    }
}
