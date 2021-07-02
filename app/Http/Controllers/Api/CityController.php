<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\City;
use Validator;
use DB;

class CityController extends Controller
{
    /**
     * Função responsável por listar os estados
     *
     * @return \Illuminate\Http\Response
     */
    public function states(Request $request)
    {
        try {
            $states = DB::table('cities')->distinct()->select('uf')->get();

            $response = ['data' => $states];

            return response()->json($response);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao realizar a listagem de estados'
            ];
            return response()->json($response, 500);
        }

    }

    /**
     * Função responsável por listar as cidades por estado
     *
     * @param  string  $state
     *
     * @return \Illuminate\Http\Response
     */
    public function cities(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'state' => 'nullable|string|min:2',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        try {
            $uf  = $request->get('state');
            $cities = City::where('uf', 'LIKE', "%$uf%")
                ->select('id', 'name')
                ->get();
            $response = ['data' => $cities];

            return response()->json($response);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao realizar a listagem de cidades'
            ];
            return response()->json($response, 500);
        }

    }
}
