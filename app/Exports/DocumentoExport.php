<?php

namespace App\Exports;

use App\Models\documento;
use App\Models\seguimiento;
use App\Models\tiempo;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Fill as StyleFill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DocumentoExport implements FromCollection,WithTitle,WithHeadings,WithStyles,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;


    public function styles(Worksheet $sheet)
    {
        $borderDashed = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A3:M3')->getFill()
        ->setFillType(StyleFill::FILL_SOLID)
        ->getStartColor()->setARGB('ACB9CA');

        $sheet->mergeCells('A2:M2');
        $sheet->mergeCells('A1:M1');
       // $sheet->getStyle('A1')->setValignment('center');
       $cell=documento::where('estado',1)->count();
       $sheet->getStyle('A1:M'.$cell+3)->ApplyFromArray($borderDashed);

    
    }
    public function title(): string
    {
        return 'TIEMPOS';
    }

    public function headings(): array
    {
        return [
            ['LISTA DE DOCUMENTOS REGISTRADOS'],
            ['FECHA DE REPORTE: '.Carbon::now()],
            [
                'N°',
                'DOCUMENTO',
                'NUMERO',
                'FECHA DE REGISTRO',
                'FECHA DE CULMINACION',
                'ASUNTO',
                'INTERESADO',
                'DNI INTERESADO',
                'DESTINO',
                'TIPO',
                'PRIORIDAD',
                'DOCUMENTO TIPO',
                'DURACION DIAS',
            ]

        ];
    }
    public function prioridad($prioridad){
        switch($prioridad){
            case 20:
                return 'NORMAL';
            case 19:
                return 'ESPECIAL';
            case 18:
                return 'URGENTE';
            case 17:
                return 'MUY URGENTE';
            default:
                return '';
        }
    }
    public function collection()
    {
        $num=1;
        $seguis= documento::where('estado',1)->get()->map(function($d) use(&$num){
            $duracion=(Carbon::parse($d->fecha)->diffInDays(Carbon::parse($d->fecha_fin)));
            return[
                'N°'=>$num++,
                'DOCUMENTO'=>$d->id,
                'NUMERO'=>$d->numero_doc,
                'FECHA DE REGISTRO'=>$d->fecha,
                'FECHA DE CULMINACION'=>$d->fecha_fin,
                'ASUNTO'=>$d->documento,
                'INTERESADO'=>$d->remitente,
                'DNI INTERESADO'=>$d->dni,
                'DESTINO'=>$d->destino,
                'TIPO'=>$d->tipo,
                'PRIORIDAD'=>$this->prioridad($d->prioridad),
                'DOCUMENTO TIPO'=>$d->tipo_doc,
                'DURACION DIAS'=>$duracion?$duracion:'0',
            ];
        });

        return $seguis;
    }
}
