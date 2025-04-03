<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller2 extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getPageSize()
    {
        if (request()->filled('per_page')) {
            return intval(request()->input('per_page'));
        }
        if (request()->filled('page_size')) {
            return intval(request()->input('page_size'));
        }
        if (request()->filled('rowsPerPage')) {
            return intval(request()->input('rowsPerPage'));
        }
        return config('controller.page_size', 20);
    }

    public function generateViewSetList(Request $request, Builder $query_Set=null, array $filterBy, array $searchBy, array $orderBy, String $nameTable=null, QueryBuilder $querySetBuilder=null)
    {
        function addOrSkipBaseTable(string $colName, string $tableBaseName)
        {
            if (strpos($colName, '.') === false) {
                return $tableBaseName . '.' . $colName;
            }
            return $colName;
        }
        $querySet = null;
        $tableBaseName = null;
        if($nameTable && $querySetBuilder){// si existe Builder Query
            $querySet= $querySetBuilder;
            $tableBaseName = $nameTable;
        }else{                      // si existe Builder Eloquent
            $querySet = $query_Set;
            $tableBaseName = $querySet->getModel()->getTable();
        }
        // filtro por columna, ejemplo: {filter_by:{notario:'valor',bien:'valor'}}
        if ($request->filled('filter_by') and $request->filter_by) {
            foreach ($request->filter_by as $index_colum => $val_filter) {
                if ($val_filter and in_array($index_colum, $filterBy, true)) {
                    $querySet->where(
                            DB::raw("TRIM(BOTH ' ' FROM REGEXP_REPLACE(".addOrSkipBaseTable($index_colum, $tableBaseName).", '[[:space:]]+', ' '))"),
                            $val_filter);
                }
            }
        }
        //fitro por rango de fechas, ejemplo: {filter_range:{anio:{from:2000,to:2015},cod_escritura:{from:5,to:20}}}
        if ($request->filled('filter_range') and $request->filter_range) {
            foreach ($request->filter_range as $index_colum => $val_colum) {
                if ($val_colum and in_array($index_colum, $filterBy, true)
                    and array_key_exists('from', $val_colum) and array_key_exists('to', $val_colum) 
                    and $val_colum['from'] and $val_colum['to']) {
                    $querySet->whereRaw("CAST(REGEXP_SUBSTR($index_colum, '[0-9]+') AS SIGNED) >= ?", [$val_colum['from']])
                             ->whereRaw("CAST(REGEXP_SUBSTR($index_colum, '[0-9]+') AS SIGNED) <= ?", [$val_colum['to']]);
                }
            }
        }
        //fitro por rango de fechas, ejemplo: {date_range:{fecha:{from:'inicioFecha',to:'finFecha'},updated_at:{from:'2000/01/01',to:'2001/01/01'}}}
        if ($request->filled('date_range') and $request->date_range) {
            foreach ($request->date_range as $index_colum => $val_colum) {
                if ($val_colum and in_array($index_colum, $filterBy, true)
                    and array_key_exists('from', $val_colum) and array_key_exists('to', $val_colum) 
                    and $val_colum['from'] and $val_colum['to']) {
                    $querySet->whereDate($index_colum, '>=', $val_colum['from'])
                             ->whereDate($index_colum, '<=', $val_colum['to']);
                }
            }
        }
        //busqueda por columna, ejemplo: {search_by:{notario:'valor',bien:'valor'}
        if ($request->filled('search_by') and $request->search_by) {
            foreach ($request->search_by as $index_colum => $val_colum) {
                if($val_colum and in_array($index_colum, $searchBy, true)){
                    $querySet->where(
                        DB::raw("TRIM(BOTH ' ' FROM REGEXP_REPLACE(".addOrSkipBaseTable($index_colum, $tableBaseName).", '[[:space:]]+', ' '))"),
                        'like',
                        '%' . $val_colum . '%'
                    );
                }
            }
        }
        //busqueda global
        if ($request->filled('search')) {
            $querySet->where(function ($q) use ($searchBy, $request, $tableBaseName) {
                foreach ($searchBy as $searchByCol) {
                    $q->orwhere(
                        DB::raw("TRIM(BOTH ' ' FROM REGEXP_REPLACE(".addOrSkipBaseTable($searchByCol, $tableBaseName).", '[[:space:]]+', ' '))"),
                        'like',
                        '%' . $request->input('search') . '%');
                }
                return $q;
            });
        }
        //ordenamiento
        if ($request->filled('order_by')) {
            $searchOrderList = explode(',', $request->input('order_by'));
            foreach ($searchOrderList as $searchOrderParam) {
                $searchOrderParamWithoutSign = preg_replace('/-/', '', $searchOrderParam, 1);
                $orderDirection = substr($searchOrderParam, 0, 1) === '-' ? 'desc' : 'asc';
                if (in_array($searchOrderParamWithoutSign, $orderBy, true)) {
                    $querySet->orderBy($searchOrderParamWithoutSign, $orderDirection);
                }
            }
        }
        // funciones de agregacion, ejemplo: {fun_agrega:[{column:'estado',fun:'COUNT',groupBy:'estado'},{column:'*',fun:'COUNT',groupBy:'user_id'},
        //      {column:'*',fun:'COUNT',where:['user_id','=','1']}]}
        $mas_datos = null;
        if ($request->filled('fun_agrega') and $request->fun_agrega) {
            $key_funcion = ['COUNT', 'SUM', 'AVG', 'MIN', 'MAX'];
            $key_operaci = ['=', '!=', '<>', '<', '>', '<=', '>='];
            foreach ($request->fun_agrega as $val) {
                if (array_key_exists('fun', $val) and $val['fun'] and in_array($val['fun'], $key_funcion, true)
                    and array_key_exists('column', $val) and $val['column'] and (in_array($val['column'], $filterBy, true) or $val['column']==='*')){
                    $query_select = null;
                    if (array_key_exists('groupBy', $val) and $val['groupBy'] and in_array($val['groupBy'], $filterBy, true)){
                        $copia_query = $querySet->getModel()->newQuery(); //clone $querySet;
                        $query_select = $copia_query->groupBy($val['groupBy'])
                            ->selectRaw($val['groupBy'].', '.$val['fun'].'('.$val['column'].') as '.'result_'.$val['fun']);
                    }else if ($val['fun']==='COUNT' or $val['column']!=='*'){
                        $copia_query = $querySet->getModel()->newQuery(); //clone $querySet;
                        $query_select = $copia_query
                            ->selectRaw($val['fun'].'('.$val['column'].') as '.'result_'.$val['fun'].'_'.($val['column']==='*'?'all':$val['column']));
                    }
                    if ($query_select) {
                        if (array_key_exists('where', $val) and $val['where'] and count($val['where'])===3 
                            and in_array($val['where'][0], $filterBy, true) and in_array($val['where'][1], $key_operaci, true)) 
                            $mas_datos[] = $query_select->whereRaw($val['where'][0].' '.$val['where'][1].' '.$val['where'][2])->get();
                        else 
                            $mas_datos[] = $query_select->get();
                    }
                }
            }
        }
        // return [$querySet->toSql(),$request->all()];
        if ($this->getPageSize()) {
            $result = $querySet->paginate($this->getPageSize()?$this->getPageSize():10);
            $json = [
                'current_page' => $result->currentPage(),
                'data' => $result->items(),
                'first_page_url' => $result->url(1),
                'from' => $result->firstItem(),
                'last_page' => $result->lastPage(),
                'last_page_url' => $result->url($result->lastPage()),
                'next_page_url' => $result->nextPageUrl(),
                'path' => $result->url($result->currentPage()),
                'per_page' => $result->perPage(),
                'prev_page_url' => $result->previousPageUrl(),
                'to' => $result->lastItem(),
                'total' => $result->total(),
            ];
            if ($mas_datos) $json['mas_datos'] = $mas_datos;
            return response()->json($json);
        }else{
            return response()->json(['data'=>$querySet->get()]);
        }
        // return $this->getPageSize() ? $querySet->paginate($this->getPageSize()) : response()->json(['data'=>$querySet->get()]);
    }

    public function QueryGenerateViewSetList(Request $request, Builder $querySet, array $filterBy, array $searchBy, array $orderBy)
    {
        $querySetSql = $querySet->toSql();
        $tableBaseName = "TempTable";
        $query = DB::table(DB::raw("($querySetSql) as $tableBaseName"))->distinct();
        return $this->generateViewSetList($request,null,$filterBy,$searchBy,$orderBy,$tableBaseName,$query);
        // return $query->select('subserie')->limit(20)->get();
    }
}
