<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $metrics;
    protected $period;
    protected $startDate;
    protected $endDate;

    public function __construct($metrics, $period, $startDate, $endDate)
    {
        $this->metrics = $metrics;
        $this->period = $period;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            // Studenti
            [
                'category' => 'Studenti',
                'metric' => 'Totali',
                'value' => $this->metrics['students']['total'],
                'period_value' => $this->metrics['students']['new'],
                'notes' => 'Studenti attivi: ' . $this->metrics['students']['active']
            ],
            [
                'category' => 'Studenti',
                'metric' => 'Nuovi (periodo)',
                'value' => $this->metrics['students']['new'],
                'period_value' => $this->metrics['students']['active'],
                'notes' => 'Percentuale attivi: ' .
                    ($this->metrics['students']['total'] > 0 ?
                        round(($this->metrics['students']['active'] / $this->metrics['students']['total']) * 100, 1) . '%' : '0%')
            ],

            // Corsi
            [
                'category' => 'Corsi',
                'metric' => 'Totali',
                'value' => $this->metrics['courses']['total'],
                'period_value' => $this->metrics['courses']['active'],
                'notes' => 'Utilizzo capacità: ' . $this->metrics['courses']['capacity_usage'] . '%'
            ],
            [
                'category' => 'Corsi',
                'metric' => 'Attivi',
                'value' => $this->metrics['courses']['active'],
                'period_value' => $this->metrics['courses']['capacity_usage'],
                'notes' => 'Capacità utilizzata'
            ],

            // Eventi
            [
                'category' => 'Eventi',
                'metric' => 'Totali',
                'value' => $this->metrics['events']['total'],
                'period_value' => $this->metrics['events']['upcoming'],
                'notes' => 'Prossimi eventi: ' . $this->metrics['events']['upcoming']
            ],
            [
                'category' => 'Eventi',
                'metric' => 'Questo periodo',
                'value' => $this->metrics['events']['this_period'],
                'period_value' => $this->metrics['events']['upcoming'],
                'notes' => 'Eventi futuri programmati'
            ],

            // Staff
            [
                'category' => 'Staff',
                'metric' => 'Totali',
                'value' => $this->metrics['staff']['total'],
                'period_value' => $this->metrics['staff']['active'],
                'notes' => 'Istruttori: ' . $this->metrics['staff']['instructors']
            ],
            [
                'category' => 'Staff',
                'metric' => 'Attivi',
                'value' => $this->metrics['staff']['active'],
                'period_value' => $this->metrics['staff']['instructors'],
                'notes' => 'Percentuale istruttori: ' .
                    ($this->metrics['staff']['total'] > 0 ?
                        round(($this->metrics['staff']['instructors'] / $this->metrics['staff']['total']) * 100, 1) . '%' : '0%')
            ],

            // Pagamenti
            [
                'category' => 'Pagamenti',
                'metric' => 'Incassi Totali (€)',
                'value' => number_format($this->metrics['payments']['total_amount'], 2),
                'period_value' => number_format($this->metrics['payments']['this_period_amount'], 2),
                'notes' => 'Periodo selezionato: €' . number_format($this->metrics['payments']['this_period_amount'], 2)
            ],
            [
                'category' => 'Pagamenti',
                'metric' => 'In Sospeso (€)',
                'value' => number_format($this->metrics['payments']['pending_amount'], 2),
                'period_value' => $this->metrics['payments']['count'],
                'notes' => 'Numero pagamenti totali: ' . $this->metrics['payments']['count']
            ],

            // Presenze
            [
                'category' => 'Presenze',
                'metric' => 'Totali',
                'value' => $this->metrics['attendance']['total'],
                'period_value' => $this->metrics['attendance']['this_period'],
                'notes' => 'Tasso presenza: ' . number_format($this->metrics['attendance']['rate'], 1) . '%'
            ],

            // Documenti
            [
                'category' => 'Documenti',
                'metric' => 'Totali',
                'value' => $this->metrics['documents']['total'],
                'period_value' => $this->metrics['documents']['pending_approval'],
                'notes' => 'Approvati: ' . $this->metrics['documents']['approved']
            ],

            // Gallerie
            [
                'category' => 'Media',
                'metric' => 'Gallerie',
                'value' => $this->metrics['galleries']['total'],
                'period_value' => $this->metrics['galleries']['total_media'],
                'notes' => 'File multimediali totali: ' . $this->metrics['galleries']['total_media']
            ],
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Categoria',
            'Metrica',
            'Valore',
            'Valore Periodo',
            'Note'
        ];
    }

    /**
     * @var mixed $row
     */
    public function map($row): array
    {
        return [
            $row['category'],
            $row['metric'],
            $row['value'],
            $row['period_value'],
            $row['notes']
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add borders
        $sheet->getStyle('A1:E' . ($this->collection()->count() + 1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Add info header
        $sheet->setCellValue('A' . ($this->collection()->count() + 3), 'Report Info:');
        $sheet->setCellValue('A' . ($this->collection()->count() + 4), 'Periodo: ' . ucfirst($this->period));
        $sheet->setCellValue('A' . ($this->collection()->count() + 5), 'Dal: ' . $this->startDate->format('d/m/Y'));
        $sheet->setCellValue('A' . ($this->collection()->count() + 6), 'Al: ' . $this->endDate->format('d/m/Y'));
        $sheet->setCellValue('A' . ($this->collection()->count() + 7), 'Generato: ' . now()->format('d/m/Y H:i'));

        return [];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Report Analytics - ' . ucfirst($this->period);
    }
}