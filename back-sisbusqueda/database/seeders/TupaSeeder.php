<?php

namespace Database\Seeders;

use App\Models\Tupa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TupaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tupa::updateOrCreate([
            'code' => '01',
            'sub_code' => '0101',
            'denominacion' => 'Búsqueda de documentos',
            'derecho_pago' => '0.00170',
            'costo' => '6.89',
        ]);
        Tupa::updateOrCreate([
            'code' => '02',
            'sub_code' => '0201',
            'denominacion' => 'Exhibicion de documentos',
            'derecho_pago' => '0.00356',
            'costo' => '14.42',
        ]);
        Tupa::updateOrCreate([
            'code' => '03',
            'sub_code' => '0103',
            'denominacion' => 'Expedicion de constancias',
            'derecho_pago' => '0.00329',
            'costo' => '13.32',
        ]);
        Tupa::updateOrCreate([
            'code' => '04',
            'sub_code' => '0401',
            'denominacion' => 'Peritaje a solicitud de partes',
            'derecho_pago' => '0.00391',
            'costo' => '15.84',
        ]);
        Tupa::updateOrCreate([
            'code' => '05',
            'sub_code' => '0501',
            'denominacion' => 'Fotocopia para usuarios',
            'derecho_pago' => '0.00005',
            'costo' => '0.20',
        ]);
        Tupa::updateOrCreate([
            'code' => '06',
            'sub_code' => '0601',
            'denominacion' => 'Prestamo de expedientes por disposicion judicial',
            'derecho_pago' => '0',
            'costo' => '0',
        ]);
        Tupa::updateOrCreate([
            'code' => '07',
            'sub_code' => '0701',
            'denominacion' => 'Testimonio por foja',
            'derecho_pago' => '0.00237',
            'costo' => '9.60',
        ]);
        Tupa::updateOrCreate([
            'code' => '07',
            'sub_code' => '0702',
            'denominacion' => 'Copia certificada por foja',
            'derecho_pago' => '0.00237',
            'costo' => '9.60',
        ]);
        Tupa::updateOrCreate([
            'code' => '07',
            'sub_code' => '0703',
            'denominacion' => 'Copia simple por foja',
            'derecho_pago' => '0.00170',
            'costo' => '6.89',
        ]);
        Tupa::updateOrCreate([
            'code' => '07',
            'sub_code' => '0704',
            'denominacion' => 'Boleta primera foja',
            'derecho_pago' => '0.00205',
            'costo' => '5.30',
        ]);
        Tupa::updateOrCreate([
            'code' => '07',
            'sub_code' => '0705',
            'denominacion' => 'Foja adicional',
            'derecho_pago' => '0.00170',
            'costo' => '6.89',
        ]);
        Tupa::updateOrCreate([
            'code' => '07',
            'sub_code' => '0706',
            'denominacion' => 'Transcripcion mecanografia',
            'derecho_pago' => '0.00293',
            'costo' => '11.78',
        ]);
        Tupa::updateOrCreate([
            'code' => '00',
            'sub_code' => '0000',
            'denominacion' => 'UIT',
            'derecho_pago' => '0.0',
            'costo' => '4950',
        ]);
    }
}
