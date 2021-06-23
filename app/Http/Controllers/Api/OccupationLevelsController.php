<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\OccupationLevel;
use Validator;
use DB;

class OccupationLevelsController extends Controller
{
    /**
     * Lista os cargos com seus níveis
     *
     * @bodyParam page int required A página solicitada (A contagem de páginas é iniciada no número zero). Exemplo: 4
     * @bodyParam length int required A quantidade de registros a serem retornados. Exemplo: 50
     * @bodyParam search string String com busca realizada pelo usuário no sistema. Exemplo: Bruno
     * @bodyParam order_by string required Define qual coluna será usada como ordenação. Exemplo: name
     * @bodyParam order_dir string required Define a ordenação da listagem: asc/desc. Exemplo: desc
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:name,created_at',
            'order_dir' => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $page     = (int) $request->get("page");
        $length   = (int) $request->get("length");
        $search   = $request->get('search');
        $orderBy  = $request->get('order_by');
        $orderDir = $request->get('order_dir');

        $occupations = DB::table('occupation_levels')
            ->join('occupations', 'occupation_levels.occupation_id', '=', 'occupations.id')
            ->join('levels', 'occupation_levels.level_id', '=', 'levels.id')
            ->select('occupation_levels.*', 'occupations.name AS occupation_name', 'levels.name AS level_name')
            ->where(function ($query) use ($search){
                $query->where('occupations.name', 'like', "%$search%")
                      ->orWhere('levels.name', 'like', "%$search%");
            })
            ->whereNull('occupation_levels.deleted_at')
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)
            ->get();

        $total = DB::table('occupation_levels')
            ->join('occupations', 'occupation_levels.occupation_id', '=', 'occupations.id')
            ->join('levels', 'occupation_levels.level_id', '=', 'levels.id')
            ->where(function ($query) use ($search){
                $query->where('occupations.name', 'like', "%$search%")
                      ->orWhere('levels.name', 'like', "%$search%");
            })
            ->whereNull('occupation_levels.deleted_at')
            ->count();

        $response = [
            "data"     => $occupations,
            "total"    => $total
        ];

        return response()->json($response);
    }

    /**
     * Relaciona um cargo a um nível.
     *
     * @bodyParam name occupation required int Nome do cargo
     * @bodyParam name level required int Status do cargo
     * @bodyParam name responsabilities required string Status do cargo
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'occupation'       => 'required|integer',
            'level'            => 'required|integer',
            'responsabilities' => 'required|string|min:2',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $idOccupation = $request->get('occupation');
        $idLevel      = $request->get('level');

        $exists = OccupationLevel::where('occupation_id', '=', $idOccupation)
            ->where('level_id', $idLevel)->get();

        if (!$exists->isEmpty()) {
            return response()->json(['message' => 'Este nível já está relacionado a este cargo'], 500);
        }

        $occupation                   = new OccupationLevel();
        $occupation->occupation_id    = $request->get('occupation');
        $occupation->level_id         = $request->get('level');
        $occupation->responsabilities = $request->get('responsabilities');

        try {
            $occupation->save();
            return response()->json(['id' => $occupation->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao relacionar o cargo a este nível'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Recupera um cargo pelo seu id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $occupation = DB::table('occupation_levels')
            ->join('occupations', 'occupation_levels.occupation_id', '=', 'occupations.id')
            ->join('levels', 'occupation_levels.level_id', '=', 'levels.id')
            ->select('occupation_levels.*', 'occupations.name AS occupation_name', 'levels.name AS level_name')
            ->where('occupation_levels.id', '=', (int)$id)
            ->whereNull('occupation_levels.deleted_at')
            ->get();

        if ($occupation->isEmpty()) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }
        return response()->json(['data' => $occupation], 200);
    }

    /**
     * Atualiza os dados do cargo.
     *
     * @param  int  $id
     *
     * @bodyParam name string required Nome do cargo
     * @bodyParam name description required Status do cargo
     * @bodyParam name status required Status do cargo
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // !!! ATENÇÃO: É PRECISO CONFIRMAR AS REGRAS DE NEGÓCIO ANTES DE REALIZAR QUALQUER ATUALIZAÇÃO !!!
        $validation = Validator::make($request->all(), [
            'occupation'       => 'required|integer',
            'level'            => 'required|integer',
            'responsabilities' => 'required|string|min:2',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $occupation = OccupationLevel::find((int)$id);
        if (!$occupation) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        $occupation                   = new OccupationLevel();
        $occupation->occupation_id    = $request->get('occupation');
        $occupation->level_id         = $request->get('level');
        $occupation->responsabilities = $request->get('responsabilities');

        try {
            $occupation->save();
            return response()->json(['id' => $occupation->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o cargo no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Remove um relacionamento entro cargo e nível.
     *
     * @param int  $id ID do cargo
     */
    public function destroy($id)
    {
        // !!! ATENÇÃO: É PRECISO CONFIRMAR AS REGRAS DE NEGÓCIO ANTES DE REALIZAR QUALQUER EXCLUSÃO !!!
        $occupation = OccupationLevel::find((int)$id);

        if (!$occupation) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        try {
            $occupation->delete();
            return response()->json(['id' => $occupation->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o registro do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }
}
