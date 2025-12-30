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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Solicitud::orderBy('updated_at', 'desc'),
            ['area_id','estado','user_id'],
            ['id'],
            ['id']
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // 1. Validar campos requeridos
            $this->validarCamposRequeridos($request);

            // 2. Crear o actualizar solicitante
            $id_solicitante = $this->crearOActualizarSolicitante($request);

            // 3. Preparar datos base de la solicitud
            $datosSolicitud = $this->prepararDatosBase($request, $id_solicitante);

            // 4. Agregar campos específicos según tipo de trámite
            $camposEspecificos = $this->obtenerCamposEspecificos($request);
            $datosSolicitud = array_merge($datosSolicitud, $camposEspecificos);

            // 5. Crear la solicitud
            $solicitud = Solicitud::create($datosSolicitud);

            // 6. ✅ CREAR REGISTRO DE BÚSQUEDA SI HAY PAGO
            $conPago = $request->boolean('con_pago', false);
            if ($conPago) {
                $this->registrarABusqueda($solicitud);
            }

            return response()->json($solicitud, 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear la solicitud',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */


    public function show(Solicitud $solicitude)
    {
        return response()->json(
            $solicitude->load(['ubigeo', 'solicitante.ubigeo', 'notario', 'notarioProceso', 'sub_serie'])
        );
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, Solicitud $solicitude)
    {
        try {
            // Solo permitir actualizar campos específicos
            $camposPermitidos = [
                'pago_busqueda',
                'estado',
                'area_id',
                'segundo_pago',
                'orden_pago'
            ];

            $solicitude->fill($request->only($camposPermitidos));

            // Si se envía con_pago, determinar estado y área
            if ($request->has('con_pago')) {
                if ($request->con_pago) {
                    $solicitude->estado = 'Buscando';
                    $solicitude->area_id = 2; // Área de búsqueda

                    // ✅ CREAR REGISTRO DE BÚSQUEDA SI NO EXISTE
                    $registroExistente = RegistroBusqueda::where('solicitud_id', $solicitude->id)->first();
                    if (!$registroExistente) {
                        $this->registrarABusqueda($solicitude);
                    }
                } else {
                    $solicitude->estado = 'Pago pendiente';
                    $solicitude->area_id = 1; // Área de recepción
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
     * Remove the specified resource.
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
     * =============================================
     * MÉTODOS DE GESTIÓN DE PAGOS
     * =============================================
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

                // ✅ CREAR REGISTRO DE BÚSQUEDA CUANDO EL PAGO ES VÁLIDO
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
     * =============================================
     * MÉTODOS PRIVADOS AUXILIARES - VALIDACIÓN
     * =============================================
     */

    private function validarCamposRequeridos(Request $request)
    {
        if (!$request->tipo_tramite) {
            throw new \Exception('El tipo de trámite es requerido');
        }

        if (!$request->num_documento) {
            throw new \Exception('El número de documento es requerido');
        }

        // Validaciones específicas por tipo de trámite
        switch ($request->tipo_tramite) {
            case 'escrituras':
                if (!$request->notario_id) {
                    throw new \Exception('El notario es requerido para escrituras');
                }
                if (!$request->otorgantes) {
                    throw new \Exception('Los otorgantes son requeridos para escrituras');
                }
                if (!$request->favorecidos) {
                    throw new \Exception('Los favorecidos son requeridos para escrituras');
                }
                break;

            case 'partidas':
                if (!$request->tipo_partida) {
                    throw new \Exception('El tipo de partida es requerido');
                }
                // Validaciones específicas por tipo de partida
                if ($request->tipo_partida === 'defuncion' && !$request->nombre_fallecido) {
                    throw new \Exception('El nombre del fallecido es requerido para partidas de defunción');
                }
                if ($request->tipo_partida === 'nacimiento' && !$request->nombre_nacido) {
                    throw new \Exception('El nombre del nacido es requerido para partidas de nacimiento');
                }
                if ($request->tipo_partida === 'matrimonio' && (!$request->nombre_esposo || !$request->nombre_esposa)) {
                    throw new \Exception('Los nombres de ambos esposos son requeridos para partidas de matrimonio');
                }
                break;

            case 'expedientes':
                if (!$request->tipo_expediente) {
                    throw new \Exception('El tipo de expediente es requerido');
                }
                if (!$request->materia_proceso) {
                    throw new \Exception('La materia del proceso es requerida para expedientes');
                }
                if (!$request->juzgado) {
                    throw new \Exception('El juzgado es requerido para expedientes');
                }
                break;

            case 'ministerio_publico':
                if (!$request->tipo_expediente_mp) {
                    throw new \Exception('El tipo de expediente es requerido para Ministerio Público');
                }
                if (!$request->fiscalia_mp) {
                    throw new \Exception('La fiscalía es requerida para Ministerio Público');
                }
                break;
        }

        // Validación de fecha mínima
        if ($request->anio && $request->anio < 1800) {
            throw new \Exception('El año debe ser mayor o igual a 1800');
        }
    }

    /**
     * =============================================
     * MÉTODOS PRIVADOS AUXILIARES - PREPARACIÓN DE DATOS
     * =============================================
     */

    private function prepararDatosBase(Request $request, $id_solicitante)
    {
        // Determinar estado y área según con_pago
        $conPago = $request->boolean('con_pago', false);

        if ($conPago) {
            $estado = 'Buscando';
            $areaId = 2; // Área de búsqueda
        } else {
            $estado = 'Pago pendiente';
            $areaId = 1; // Área de recepción
        }

        // Para escrituras, el ubigeo viene del notario, para otros trámites es manual
        $ubigeo_cod = null;
        if ($request->tipo_tramite === 'escrituras') {
            $ubigeo_cod = $request->ubigeo_cod_soli ?: null;
        } else {
            $ubigeo_cod = $request->ubigeo_cod_soli ?: null;
        }

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

    private function generarFecha($anio, $mes, $dia)
    {
        if (!$anio) return null;

        // Validar que la fecha sea válida
        $mes = $mes ?: '01';
        $dia = $dia ?: '01';

        if (!checkdate((int)$mes, (int)$dia, (int)$anio)) {
            return $anio . '/01/01'; // Fecha por defecto si no es válida
        }

        return $anio . '/' .
               str_pad($mes, 2, '0', STR_PAD_LEFT) . '/' .
               str_pad($dia, 2, '0', STR_PAD_LEFT);
    }

    private function obtenerCamposEspecificos(Request $request)
    {
        $campos = [];

        switch ($request->tipo_tramite) {
            case 'escrituras':
                $campos = $this->getCamposEscrituras($request);
                break;
            case 'partidas':
                $campos = $this->getCamposPartidas($request);
                break;
            case 'expedientes':
                $campos = $this->getCamposExpedientes($request);
                break;
            case 'enace':
                $campos = $this->getCamposEnace($request);
                break;
            case 'impuesto':
                $campos = $this->getCamposImpuesto($request);
                break;
            case 'procesos':
                $campos = $this->getCamposProcesos($request);
                break;
            case 'ministerio_publico':
                $campos = $this->getCamposMinisterioPublico($request);
                break;
        }

        // Asegurarse de que TODOS los campos existan con valores null si no están presentes
        $todosLosCampos = [
            // Campos de escrituras
            'notario_id', 'subserie_id', 'otorgantes', 'favorecidos', 'bien',
            'sescritura', 'sprotocolo', 'sfolio',
            // Campos de partidas
            'tipo_partida', 'nombre_fallecido', 'nombre_nacido', 'nombre_esposo', 'nombre_esposa',
            // Campos de expedientes
            'tipo_expediente', 'materia_proceso', 'demandante', 'demandado', 'causante',
            'juzgado', 'secretario',
            // Campos de ENACE
            'contrato_privado', 'otorgante_enace', 'favorecido_enace', 'institucion_enace',
            // Campos de IMPUESTO
            'causante_impuesto', 'direccion_impuesto',
            // Campos de PROCESOS
            'proceso_de', 'en_contra_de', 'causante_proceso', 'notario_proceso',
            // Campos de MINISTERIO PÚBLICO
            'tipo_expediente_mp', 'caso_mp', 'area_mp', 'materia_mp', 'agraviado_mp',
            'imputado_mp', 'fiscalia_mp', 'numero_caso_mp', 'numero_paquete_mp'
        ];

        // Inicializar todos los campos con null si no existen
        foreach ($todosLosCampos as $campo) {
            if (!array_key_exists($campo, $campos)) {
                $campos[$campo] = null;
            }
        }

        return $campos;
    }

    /**
     * =============================================
     * MÉTODOS PRIVADOS AUXILIARES - CAMPOS ESPECÍFICOS
     * =============================================
     */

    private function getCamposEscrituras(Request $request)
    {
        return [
            'notario_id' => $request->notario_id ?: null,
            'subserie_id' => $request->subserie_id ?: null,
            'otorgantes' => $request->otorgantes ?: null,
            'favorecidos' => $request->favorecidos ?: null,
            'bien' => $request->bien ?: null,
            'sescritura' => $request->sescritura ?: null,
            'sprotocolo' => $request->sprotocolo ?: null,
            'sfolio' => $request->sfolio ?: null
        ];
    }

    private function getCamposPartidas(Request $request)
    {
        return [
            'tipo_partida' => $request->tipo_partida ?: null,
            'nombre_fallecido' => $request->nombre_fallecido ?: null,
            'nombre_nacido' => $request->nombre_nacido ?: null,
            'nombre_esposo' => $request->nombre_esposo ?: null,
            'nombre_esposa' => $request->nombre_esposa ?: null
        ];
    }

    private function getCamposExpedientes(Request $request)
    {
        return [
            'tipo_expediente' => $request->tipo_expediente ?: null,
            'materia_proceso' => $request->materia_proceso ?: null,
            'demandante' => $request->demandante ?: null,
            'demandado' => $request->demandado ?: null,
            'causante' => $request->causante ?: null,
            'juzgado' => $request->juzgado ?: null,
            'secretario' => $request->secretario ?: null
        ];
    }

    private function getCamposEnace(Request $request)
    {
        return [
            'contrato_privado' => $request->contrato_privado ?: null,
            'otorgante_enace' => $request->otorgante_enace ?: null,
            'favorecido_enace' => $request->favorecido_enace ?: null,
            'institucion_enace' => $request->institucion_enace ?: null
        ];
    }

    private function getCamposImpuesto(Request $request)
    {
        return [
            'causante_impuesto' => $request->causante_impuesto ?: null,
            'direccion_impuesto' => $request->direccion_impuesto ?: null
        ];
    }

    private function getCamposProcesos(Request $request)
    {
        return [
            'proceso_de' => $request->proceso_de ?: null,
            'en_contra_de' => $request->en_contra_de ?: null,
            'causante_proceso' => $request->causante_proceso ?: null,
            'notario_proceso' => $request->notario_proceso ?: null
        ];
    }

    private function getCamposMinisterioPublico(Request $request)
    {
        return [
            'tipo_expediente_mp' => $request->tipo_expediente_mp ?: null,
            'caso_mp' => $request->caso_mp ?: null,
            'area_mp' => $request->area_mp ?: null,
            'materia_mp' => $request->materia_mp ?: null,
            'agraviado_mp' => $request->agraviado_mp ?: null,
            'imputado_mp' => $request->imputado_mp ?: null,
            'fiscalia_mp' => $request->fiscalia_mp ?: null,
            'numero_caso_mp' => $request->numero_caso_mp ?: null,
            'numero_paquete_mp' => $request->numero_paquete_mp ?: null
        ];
    }

    /**
     * =============================================
     * MÉTODOS PRIVADOS AUXILIARES - SOLICITANTE Y BÚSQUEDA
     * =============================================
     */

    private function crearOActualizarSolicitante(Request $request)
    {
        $existeSolicitante = Solicitante::where('num_documento', $request->num_documento)->first();

        $datosSolicitante = [
            'nombres' => $request->nombres ?: null,
            'apellido_paterno' => $request->apellido_paterno ?: null,
            'apellido_materno' => $request->apellido_materno ?: null,
            'nombre_completo' => $request->nombre_completo ?: null,
            'asunto' => $request->asunto ?: null,
            'tipo_documento' => $request->tipo_documento,
            'num_documento' => $request->num_documento,
            'direccion' => $request->direccion,
            'correo' => $request->correo,
            'celular' => $request->celular,
            'ubigeo_cod' => $request->ubigeo_cod
        ];

        if ($existeSolicitante) {
            $existeSolicitante->update($datosSolicitante);
            return $existeSolicitante->id;
        } else {
            $nuevoSolicitante = Solicitante::create($datosSolicitante);
            return $nuevoSolicitante->id;
        }
    }

    /**
     * Crea un registro de búsqueda para la solicitud.
     */
    private function registrarABusqueda(Solicitud $solicitud)
    {
        return RegistroBusqueda::create([
            'solicitud_id' => $solicitud->id,
            'estado' => 0, // 0 = Pendiente de búsqueda
        ]);
    }

    /**
     * =============================================
     * MÉTODO PARA LISTADO CON FILTROS Y PAGINACIÓN
     * =============================================
     */

    public function generateViewSetList(Request $request, Builder $querySet,
        array $filterBy,
        array $searchBy,
        array $orderBy,
        array $relationFields = []
    ) {
        foreach ($filterBy as $field) {
            if ($request->filled($field)) {
                $querySet->where($field, $request->input($field));
            }
        }

        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            $querySet->where(function($q) use ($searchBy, $filter) {
                foreach ($searchBy as $field) {
                    $q->orWhere($field, 'like', "%$filter%");
                }
                $q->orWhereHas('solicitante', function($q2) use ($filter) {
                    $q2->where('num_documento', 'like', "%$filter%")
                       ->orWhere('nombre_completo', 'like', "%$filter%")
                       ->orWhere('asunto', 'like', "%$filter%");
                });
            });
        }

        $sortBy = $request->input('sortBy', $orderBy[0] ?? 'id');
        $direction = $request->boolean('descending', true) ? 'desc' : 'asc';

        if (in_array($sortBy, $orderBy)) {
            $querySet->orderBy($sortBy, $direction);
        }

        $perPage = $request->input('rowsPerPage', 7);

        if ($perPage == -1) {
            $data = $querySet->get();
            return response()->json([
                'data' => $data,
                'total' => $data->count(),
                'current_page' => 1,
                'per_page' => $data->count(),
            ]);
        }

        $paginated = $querySet->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }
}
