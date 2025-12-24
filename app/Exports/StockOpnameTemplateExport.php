<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockOpnameTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['product_code'];
    }

    public function array(): array
    {
        return [
            ['P00001'],
            ['P00002'],
        ];
    }
}
