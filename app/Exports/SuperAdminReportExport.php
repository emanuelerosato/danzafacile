<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class SuperAdminReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        return collect($this->data['items'])->map(function ($item) {
            return collect($item);
        });
    }

    public function headings(): array
    {
        if (empty($this->data['items'])) {
            return ['Nessun dato disponibile'];
        }

        $firstItem = collect($this->data['items'])->first();
        return array_keys($firstItem);
    }

    public function title(): string
    {
        return $this->data['title'] ?? 'Report';
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('1:1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        // Add summary information at the top
        if (!empty($this->data['summary'])) {
            $row = 1;
            $sheet->insertNewRowBefore(1, count($this->data['summary']) + 2);
            
            foreach ($this->data['summary'] as $key => $value) {
                $sheet->setCellValue('A' . $row, ucfirst(str_replace('_', ' ', $key)));
                $sheet->setCellValue('B' . $row, $value);
                $row++;
            }
            
            // Style summary section
            $summaryRange = 'A1:B' . ($row - 1);
            $sheet->getStyle($summaryRange)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6'],
                ],
            ]);
        }

        return [];
    }
}