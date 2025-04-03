<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSolicitanteRequest;
use App\Models\Solicitante;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SolicitanteController extends Controller
{
   
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
        // Permission::find($id)->update($request->all());
        return response()->json([$request, $solicitude]);
    }

   
    public function destroy(Solicitante $solicitude)
    {
        return response()->json($solicitude->delete());
    }

    public function getSolicitanteDni(string $dni)
    {
        $persona = Solicitante::where('num_documento', $dni)->first();
        if ($persona) {
            $persona->existe = true;
            return response()->json($persona);
        } else {
            $token = 'apis-token-6807.Z1QDuGGlyyYJEtNrFCo1TmgDHOx54FNE';
            // $numero = '46027897';
            $client = new Client(['base_uri' => 'https://api.apis.net.pe', 'verify' => false]);
            $parameters = [
                'http_errors' => false,
                'connect_timeout' => 5,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Referer' => 'https://apis.net.pe/dnisolicitante',
                    'User-Agent' => 'laravel/guzzle',
                    'Accept' => 'application/json',
                ],
                'query' => ['numero' => $dni]
            ];
            // Para usar la versión 1 de la api, cambiar a /v1/dni
            $res = $client->request('GET', '/v2/reniec/dni', $parameters);
            $response = json_decode($res->getBody()->getContents(), true);

            return response()->json($response);
        }
    }
}
