<?php

namespace App\Http\Controllers;

use App\Models\Precio;
use App\Models\RegistroBusqueda;
use App\Models\Solicitante;
use App\Models\Solicitud;
use App\Models\Tupa;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SolicitudController extends Controller
{
    /**
     * =====================================================
     * MÉTODOS PRINCIPALES DEL RECURSO SOLICITUD
     * =====================================================
     */

    /**
     * Listado de solicitudes con filtros, búsqueda y paginación.
     */
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Solicitud::orderBy('updated_at', 'desc'),
            ['area_id','estado','user_id'], // filtros exactos
            ['id'],                        // campos para búsqueda
            ['id']                         // campos permitidos para ordenar
        );
    }

    /**
     * Crear una nueva solicitud.
     */
    // public function store(Request $request)
    // {
    //     try {
    //         // 1️⃣ Validar campos obligatorios según tipo de trámite
    //         $this->validarCamposRequeridos($request);

    //         // 2️⃣ Crear o actualizar solicitante
    //         $id_solicitante = $this->crearOActualizarSolicitante($request);

    //         // 3️⃣ Preparar datos base de la solicitud
    //         $datosSolicitud = $this->prepararDatosBase($request, $id_solicitante);

    //         // 4️⃣ Agregar campos específicos por tipo de trámite
    //         $camposEspecificos = $this->obtenerCamposEspecificos($request);
    //         $datosSolicitud = array_merge($datosSolicitud, $camposEspecificos);

    //         // 5️⃣ Generar código único de solicitud por año
    //         $datosSolicitud['solicitud_code'] = $this->generarCodigoSolicitud($request->anio);

    //         // 6️⃣ Crear la solicitud
    //         $solicitud = Solicitud::create($datosSolicitud);

    //         // 7️⃣ Registrar búsqueda si tiene pago
    //         if ($request->boolean('con_pago', false)) {
    //             $this->registrarABusqueda($solicitud);
    //         }

    //         return response()->json($solicitud, 201);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Error al crear la solicitud',
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        try {
            $this->validarCamposRequeridos($request);
            $id_solicitante = $this->crearOActualizarSolicitante($request);

            $datosSolicitud = $this->prepararDatosBase($request, $id_solicitante);
            $camposEspecificos = $this->obtenerCamposEspecificos($request);
            $datosSolicitud = array_merge($datosSolicitud, $camposEspecificos);

            // Generar código de solicitud
            $datosSolicitud['solicitud_code'] = $this->generarCodigoSolicitud();

            $solicitud = Solicitud::create($datosSolicitud);

            if ($request->boolean('con_pago', false)) {
                $this->registrarABusqueda($solicitud);
            }

            // Devolver con relaciones cargadas
            $solicitud->load(['ubigeo', 'solicitante.ubigeo', 'notario', 'subSerie', 'user.area']);

            return response()->json($solicitud, 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear la solicitud',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Mostrar los detalles de una solicitud.
     */
    public function show(Solicitud $solicitude)
    {
        return response()->json(
            $solicitude->load(['ubigeo', 'solicitante.ubigeo', 'notario', 'notarioProceso', 'subSerie'])
        );
    }

    /**
     * Actualizar ciertos campos de la solicitud.
     */
    public function update(Request $request, Solicitud $solicitude)
    {
        try {
            $camposPermitidos = [
                'pago_busqueda', 'estado', 'area_id', 'segundo_pago', 'orden_pago'
            ];

            $solicitude->fill($request->only($camposPermitidos));

            // Manejo de con_pago
            if ($request->has('con_pago')) {
                if ($request->con_pago) {
                    $solicitude->estado = 'Buscando';
                    $solicitude->area_id = 2;

                    // Registrar búsqueda si no existe
                    if (!RegistroBusqueda::where('solicitud_id', $solicitude->id)->first()) {
                        $this->registrarABusqueda($solicitude);
                    }
                } else {
                    $solicitude->estado = 'Pago pendiente';
                    $solicitude->area_id = 1;
                }
            }

            $solicitude->save();
            return response()->json($solicitude);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar la solicitud',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una solicitud.
     */
    public function destroy(Solicitud $solicitude)
    {
        try {
            return response()->json($solicitude->delete());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar la solicitud',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =====================================================
     * MÉTODOS DE GESTIÓN DE PAGOS
     * =====================================================
     */

    public function registrarPagoBusqueda($solicitudId, $pagoId)
    {
        try {
            $solicitud = Solicitud::findOrFail($solicitudId);
            $pago = Pago::findOrFail($pagoId);

            $tupaBusqueda = $pago->tupas()
                ->where('code', '01')
                ->where('sub_code', '0101')
                ->first();

            if ($tupaBusqueda) {
                $solicitud->pago_busqueda = $pago->id;
                $solicitud->estado = 'Buscando';
                $solicitud->area_id = 2;
                $this->registrarABusqueda($solicitud);
            } else {
                $solicitud->estado = 'Pago pendiente';
            }

            $solicitud->save();
            return response()->json($solicitud);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar el pago',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarSegundoPago($solicitudId, $pagoId)
    {
        try {
            $solicitud = Solicitud::findOrFail($solicitudId);
            $pago = Pago::findOrFail($pagoId);

            $solicitud->segundo_pago = $pago->id;
            $solicitud->estado = 'Finalizado';
            $solicitud->save();

            return response()->json($solicitud);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar el segundo pago',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function asignarOrdenPago($solicitudId, $pagoId)
    {
        try {
            $solicitud = Solicitud::findOrFail($solicitudId);
            $pago = Pago::findOrFail($pagoId);

            $solicitud->orden_pago = $pago->id;
            $solicitud->estado = 'Por pagar';
            $solicitud->save();

            return response()->json($solicitud);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al asignar orden de pago',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * =====================================================
     * MÉTODOS PRIVADOS AUXILIARES
     * =====================================================
     */

    /**
     * Validar campos requeridos según tipo de trámite.
     */
    private function validarCamposRequeridos(Request $request)
    {
        if (!$request->tipo_tramite) throw new \Exception('El tipo de trámite es requerido');
        if (!$request->num_documento) throw new \Exception('El número de documento es requerido');

        switch ($request->tipo_tramite) {
            case 'escrituras':
                if (!$request->notario_id) throw new \Exception('El notario es requerido para escrituras');
                if (!$request->otorgantes) throw new \Exception('Los otorgantes son requeridos para escrituras');
                if (!$request->favorecidos) throw new \Exception('Los favorecidos son requeridos para escrituras');
                break;

            case 'partidas':
                if (!$request->tipo_partida) throw new \Exception('El tipo de partida es requerido');
                if ($request->tipo_partida === 'defuncion' && !$request->nombre_fallecido) {
                    throw new \Exception('El nombre del fallecido es requerido para defunción');
                }
                if ($request->tipo_partida === 'nacimiento' && !$request->nombre_nacido) {
                    throw new \Exception('El nombre del nacido es requerido para nacimiento');
                }
                if ($request->tipo_partida === 'matrimonio' && (!$request->nombre_esposo || !$request->nombre_esposa)) {
                    throw new \Exception('Los nombres de ambos esposos son requeridos para matrimonio');
                }
                break;

            case 'expedientes':
                if (!$request->tipo_expediente) throw new \Exception('El tipo de expediente es requerido');
                if (!$request->materia_proceso) throw new \Exception('La materia del proceso es requerida');
                if (!$request->juzgado) throw new \Exception('El juzgado es requerido');
                break;

            case 'ministerio_publico':
                if (!$request->tipo_expediente_mp) throw new \Exception('El tipo de expediente es requerido');
                if (!$request->fiscalia_mp) throw new \Exception('La fiscalía es requerida');
                break;
        }

        if ($request->anio && $request->anio < 1800) {
            throw new \Exception('El año debe ser mayor o igual a 1800');
        }
    }

    /**
     * Preparar datos base de la solicitud.
     */
    private function prepararDatosBase(Request $request, $id_solicitante)
    {
        $conPago = $request->boolean('con_pago', false);
        $estado = $conPago ? 'Buscando' : 'Pago pendiente';
        $areaId = $conPago ? 2 : 1;

        $ubigeo_cod = $request->ubigeo_cod_soli ?: null;

        return [
            'tipo_tramite' => $request->tipo_tramite,
            'solicitante_id' => $id_solicitante,
            'anio' => $request->anio,
            'mes' => $request->mes ?: null,
            'dia' => $request->dia ?: null,
            'fecha' => $this->generarFecha($request->anio, $request->mes, $request->dia),
            'ubigeo_cod' => $ubigeo_cod,
            'mas_datos' => $request->mas_datos ?: null,
            'area_id' => $areaId,
            'estado' => $estado,
            'user_id' => auth()->user()->id,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    /**
     * Generar fecha válida a partir de año, mes y día.
     */
    private function generarFecha($anio, $mes, $dia)
    {
        if (!$anio) return null;
        $mes = $mes ?: '01';
        $dia = $dia ?: '01';
        if (!checkdate((int)$mes, (int)$dia, (int)$anio)) {
            return $anio . '/01/01';
        }
        return $anio . '/' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' . str_pad($dia, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener campos específicos según tipo de trámite.
     */
    private function obtenerCamposEspecificos(Request $request)
    {
        $campos = [];
        switch ($request->tipo_tramite) {
            case 'escrituras': $campos = $this->getCamposEscrituras($request); break;
            case 'partidas': $campos = $this->getCamposPartidas($request); break;
            case 'expedientes': $campos = $this->getCamposExpedientes($request); break;
            case 'enace': $campos = $this->getCamposEnace($request); break;
            case 'impuesto': $campos = $this->getCamposImpuesto($request); break;
            case 'procesos': $campos = $this->getCamposProcesos($request); break;
            case 'ministerio_publico': $campos = $this->getCamposMinisterioPublico($request); break;
        }

        // Inicializar todos los campos posibles con null
        $todosLosCampos = array_merge(
            array_keys($campos),
            ['notario_id','subserie_id','otorgantes','favorecidos','bien','sescritura','sprotocolo','sfolio',
                'tipo_partida','nombre_fallecido','nombre_nacido','nombre_esposo','nombre_esposa',
                'tipo_expediente','materia_proceso','demandante','demandado','causante','juzgado','secretario',
                'contrato_privado','otorgante_enace','favorecido_enace','institucion_enace',
                'causante_impuesto','direccion_impuesto',
                'proceso_de','en_contra_de','causante_proceso','notario_proceso',
                'tipo_expediente_mp','caso_mp','area_mp','materia_mp','agraviado_mp','imputado_mp','fiscalia_mp','numero_caso_mp','numero_paquete_mp']
        );

        foreach ($todosLosCampos as $campo) {
            if (!array_key_exists($campo, $campos)) $campos[$campo] = null;
        }

        return $campos;
    }

    /**
     * Generar código único de solicitud por año (reinicia cada año).
     */
    private function generarCodigoSolicitud()
    {
        $anioActual = date('Y');

        // Obtener el último registro del año actual por fecha de creación
        $ultimo = Solicitud::whereYear('created_at', $anioActual)
            ->orderBy('created_at', 'desc')
            ->first();

        // Determinar el siguiente número
        $numero = (!$ultimo || !$ultimo->solicitud_code)
            ? 1
            : intval(explode('-', $ultimo->solicitud_code)[0]) + 1;

        // Formatear código con ceros a la izquierda
        return str_pad($numero, 6, '0', STR_PAD_LEFT) . '-' . $anioActual;
    }


    /**
     * =====================================================
     * CAMPOS ESPECÍFICOS POR TIPO DE TRÁMITE
     * =====================================================
     */

    private function getCamposEscrituras(Request $request) { return [
        'notario_id'=>$request->notario_id,'subserie_id'=>$request->subserie_id,
        'otorgantes'=>$request->otorgantes,'favorecidos'=>$request->favorecidos,
        'bien'=>$request->bien,'sescritura'=>$request->sescritura,
        'sprotocolo'=>$request->sprotocolo,'sfolio'=>$request->sfolio
    ];}

    private function getCamposPartidas(Request $request) { return [
        'tipo_partida'=>$request->tipo_partida,'nombre_fallecido'=>$request->nombre_fallecido,
        'nombre_nacido'=>$request->nombre_nacido,'nombre_esposo'=>$request->nombre_esposo,
        'nombre_esposa'=>$request->nombre_esposa
    ];}

    private function getCamposExpedientes(Request $request) { return [
        'tipo_expediente'=>$request->tipo_expediente,'materia_proceso'=>$request->materia_proceso,
        'demandante'=>$request->demandante,'demandado'=>$request->demandado,
        'causante'=>$request->causante,'juzgado'=>$request->juzgado,'secretario'=>$request->secretario
    ];}

    private function getCamposEnace(Request $request) { return [
        'contrato_privado'=>$request->contrato_privado,'otorgante_enace'=>$request->otorgante_enace,
        'favorecido_enace'=>$request->favorecido_enace,'institucion_enace'=>$request->institucion_enace
    ];}

    private function getCamposImpuesto(Request $request) { return [
        'causante_impuesto'=>$request->causante_impuesto,'direccion_impuesto'=>$request->direccion_impuesto
    ];}

    private function getCamposProcesos(Request $request) { return [
        'proceso_de'=>$request->proceso_de,'en_contra_de'=>$request->en_contra_de,
        'causante_proceso'=>$request->causante_proceso,'notario_proceso'=>$request->notario_proceso
    ];}

    private function getCamposMinisterioPublico(Request $request) { return [
        'tipo_expediente_mp'=>$request->tipo_expediente_mp,'caso_mp'=>$request->caso_mp,
        'area_mp'=>$request->area_mp,'materia_mp'=>$request->materia_mp,'agraviado_mp'=>$request->agraviado_mp,
        'imputado_mp'=>$request->imputado_mp,'fiscalia_mp'=>$request->fiscalia_mp,
        'numero_caso_mp'=>$request->numero_caso_mp,'numero_paquete_mp'=>$request->numero_paquete_mp
    ];}

    /**
     * Crear o actualizar solicitante.
     */
    private function crearOActualizarSolicitante(Request $request)
    {
        $existe = Solicitante::where('num_documento',$request->num_documento)->first();

        $datos = [
            'nombres'=>$request->nombres,'apellido_paterno'=>$request->apellido_paterno,
            'apellido_materno'=>$request->apellido_materno,'nombre_completo'=>$request->nombre_completo,
            'asunto'=>$request->asunto,'tipo_documento'=>$request->tipo_documento,
            'num_documento'=>$request->num_documento,'direccion'=>$request->direccion,
            'correo'=>$request->correo,'celular'=>$request->celular,'ubigeo_cod'=>$request->ubigeo_cod
        ];

        if ($existe) {
            $existe->update($datos);
            return $existe->id;
        } else {
            return Solicitante::create($datos)->id;
        }
    }

    /**
     * Registrar un registro de búsqueda asociado a la solicitud.
     */
    private function registrarABusqueda(Solicitud $solicitud)
    {
        return RegistroBusqueda::create([
            'solicitud_id'=>$solicitud->id,
            'estado'=>0
        ]);
    }

    /**
     * =====================================================
     * MÉTODO DE LISTADO CON FILTROS Y PAGINACIÓN
     * =====================================================
     */
    public function generateViewSetList(Request $request, Builder $querySet,
        array $filterBy, array $searchBy, array $orderBy, array $relationFields = []
    ) {
        // Aplicar filtros exactos
        foreach ($filterBy as $field) {
            if ($request->filled($field)) $querySet->where($field, $request->input($field));
        }

        // Aplicar búsqueda por campos
        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            $querySet->where(function($q) use ($searchBy,$filter) {
                foreach ($searchBy as $field) $q->orWhere($field,'like',"%$filter%");
                $q->orWhereHas('solicitante', function($q2) use ($filter) {
                    $q2->where('num_documento','like',"%$filter%")
                        ->orWhere('nombre_completo','like',"%$filter%")
                        ->orWhere('asunto','like',"%$filter%");
                });
            });
        }

        // Ordenamiento
        $sortBy = $request->input('sortBy', $orderBy[0] ?? 'id');
        $direction = $request->boolean('descending', true) ? 'desc' : 'asc';
        if (in_array($sortBy, $orderBy)) $querySet->orderBy($sortBy,$direction);

        // Paginación
        $perPage = $request->input('rowsPerPage',7);
        if ($perPage == -1) {
            $data = $querySet->get();
            return response()->json([
                'data'=>$data,'total'=>$data->count(),'current_page'=>1,'per_page'=>$data->count()
            ]);
        }
        $paginated = $querySet->paginate($perPage);
        return response()->json([
            'data'=>$paginated->items(),'total'=>$paginated->total(),
            'current_page'=>$paginated->currentPage(),'per_page'=>$paginated->perPage()
        ]);
    }
}
