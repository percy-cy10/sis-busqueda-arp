<?php

namespace App\Http\Controllers\Sis_Anterior;

use App\Http\Controllers\Controller2;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Sis_AnteriorModels\Anterior;
use App\Models\Sis_AnteriorModels\Anterior2;
use App\Models\Sis_AnteriorModels\Arbolito;
use App\Models\Sis_AnteriorModels\Nuevo;
use App\Models\Sis_AnteriorModels\Nuevo2;
use App\Models\Sis_AnteriorModels\Sia;
use App\Models\Sis_AnteriorModels\Sis2018;
use App\Models\Sis_AnteriorModels\Sis2018_2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateListController extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->filled('table') || !$request->filled('column'))
            return [];
        elseif ($request->table === 'all')
            return $this->generateListAll($request->column);
        elseif ($request->table === 'anterior'){
            return $this->generateSelectList(Anterior::select($request->column)->distinct(),
                $request->column);
        }elseif ($request->table === 'anterior2'){
            return $this->generateSelectList(Anterior2::select($request->column)->distinct(),
                $request->column);
        }elseif ($request->table === 'sis2018'){
            return $this->generateSelectList(Sis2018::select($request->column)->distinct(),
                $request->column);
        }elseif ($request->table === 'sis2018_2'){
            return $this->generateSelectList(Sis2018_2::select($request->column)->distinct(),
                $request->column);
        }elseif ($request->table === 'nuevo'){
            return $this->generateSelectList(Nuevo::select($request->column)->distinct(),
                $request->column);
        }elseif ($request->table === 'nuevo2'){
            return $this->generateSelectList(Nuevo2::select($request->column)->distinct(),
                $request->column);
        }elseif ($request->table === 'sia'){
            return $this->generateSelectList(Sia::select($request->column)->distinct(),
                $request->column);
        }elseif ($request->table === 'arbolito'){
            return $this->generateSelectList(Arbolito::select($request->column)->distinct(),
                $request->column);
        }else {
            return [];
        }
    }
    private function generateListAll(String $column){
        $tempTable = Anterior::select($column)->distinct()
            ->unionAll(Anterior2::select($column)->distinct())
            ->unionAll(Sis2018::select($column)->distinct())
            ->unionAll(Sis2018_2::select($column)->distinct())
            ->unionAll(Nuevo::select($column)->distinct())
            ->unionAll(Nuevo2::select($column)->distinct());

        return $this->generateSelectList($tempTable,$column);
    }
    public function generateSelectList(Builder $querySet,String $column){
        $querySetSql = $querySet->toSql();
        return DB::table(DB::raw("($querySetSql) as TempTable"))
            ->select(DB::raw("TRIM(BOTH ' ' FROM REGEXP_REPLACE(CONCAT(' ', ".$column.", ' '), '[[:space:]]+', ' ')) as ".$column))
            ->distinct()->whereNotNull($column)
            ->orderBy("$column",'asc')
            ->get();
    }
    /****** pruebas paragenrar union de varias tablas ******************************************************************** */
    public function generateTableAll(Request $request)
    {
        $tempTable = Anterior::select("*")
            ->unionAll(Anterior2::select("*"))
            ->unionAll(Sis2018::select("*"))
            ->unionAll(Sis2018_2::select("*"))
            ->unionAll(Nuevo::select("*"))
            ->unionAll(Nuevo2::select("*"));

        // $request2 = new Request(['filter_by'=>['fecha'=>'1976-11-19']]);

        return $this->QueryGenerateViewSetList(
            $request,
            $tempTable,
            ['notario','lugar','subserie','otorgantes','fecha'], //para el filtrado
            ['id','notario',],  //para la busqueda
            ['id','notario','lugar','subserie','fecha','bien','protocolo'] //para el odenamiento
        );
    }


}
