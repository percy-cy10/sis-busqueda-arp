<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitanteRequest;
use App\Models\Solicitante;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SolicitanteController extends Controller
{
    private $token_dni = 'apis-token-6807.Z1QDuGGlyyYJEtNrFCo1TmgDHOx54FNE';
    private $token_ruc = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InBlY29uZG9yaXl1QGVzdC51bmFwLmVkdS5wZSJ9.yXCt9oouWEMiTosfoRd2jZlunmWKVyk37UDvu-N7psM';

    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Solicitante::query(),
            [],
            ['id', 'nombre'],
            ['id', 'nombre']
        );
    }

    public function store(StoreSolicitanteRequest $request)
    {
        return response(Solicitante::create($request->all()), 201);
    }

    public function show(Int $id)
    {
        return response()->json(Solicitante::find($id));
    }

    public function update(Request $request, Solicitante $solicitude)
    {
        $solicitude->update($request->all());
        return response()->json([$request, $solicitude]);
    }

    public function destroy(Solicitante $solicitude)
    {
        return response()->json($solicitude->delete());
    }

    /**
     * 🔍 Consulta DNI en la API de APIS.NET.PE
     */
    public function getSolicitanteDni(string $dni)
    {
        $persona = Solicitante::where('num_documento', $dni)->first();
        if ($persona) {
            $persona->existe = true;
            return response()->json($persona);
        }

        try {
            $client = new Client(['base_uri' => 'https://api.apis.net.pe', 'verify' => false]);
            $parameters = [
                'http_errors' => false,
                'connect_timeout' => 5,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token_dni,
                    'Referer' => 'https://apis.net.pe/dnisolicitante',
                    'User-Agent' => 'laravel/guzzle',
                    'Accept' => 'application/json',
                ],
                'query' => ['numero' => $dni]
            ];

            $res = $client->request('GET', '/v2/reniec/dni', $parameters);
            $response = json_decode($res->getBody()->getContents(), true);

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al consultar el servicio DNI: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 🔍 Consulta RUC en la API de ApisPeru
     */
    public function getSolicitanteRuc(string $ruc)
    {
        try {
            $client = new Client(['verify' => false]);
            $url = "https://dniruc.apisperu.com/api/v1/ruc/{$ruc}?token={$this->token_ruc}";
            $res = $client->get($url);
            $data = json_decode($res->getBody(), true);

            if (isset($data['ruc'])) {
                return response()->json($data);
            } else {
                return response()->json(['error' => 'RUC no encontrado o inválido.'], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al consultar el servicio RUC: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 📄 Consultar todas las solicitudes por DNI
     */
    public function solicitudesPorDni($dni)
    {
        $solicitante = Solicitante::where('num_documento', $dni)->first();

        if (!$solicitante) {
            return response()->json([
                'message' => 'Solicitante no encontrado',
                'dni' => $dni,
                'solicitudes_count' => 0,
                'solicitudes_ids' => []
            ], 404);
        }

        $solicitudes = $solicitante->solicitudes()->get();

        return response()->json([
            'dni' => $dni,
            'solicitante' => $solicitante,
            'solicitudes_count' => $solicitudes->count(),
            'solicitudes' => $solicitudes
        ]);
    }
}
