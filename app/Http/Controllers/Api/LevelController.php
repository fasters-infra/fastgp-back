<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DBServices;

use App\Models\Level;
use Validator;

class LevelController extends Controller
{
    /**
     * Listagem de níveis
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
        $status   = $request->get('status');
        $orderBy  = $request->get('order_by');
        $orderDir = $request->get('order_dir');

        $level = Level::where('name', 'like', "%$search%")
            ->where('status', 'like', "%$status%")
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $total = Level::where('name', 'like', "%$search%")
            ->where('status', 'like', "%$status%")
            ->count();

        $response = [
            "data"     => $level,
            "total"    => $total
        ];

        return response()->json($response);
    }

    /**
     * Inclui um novo nível.
     *
     * @bodyParam name string required Nome do nível
     * @bodyParam name status required Status do nível
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $enumValues = new DBServices();
        $enumValues = $enumValues->getEnumValues('users', 'status');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'name'             => 'required|string|min:2',
            'status'           => "required|string{$enumValues}",
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $level                   = new Level();
        $level->name             = $request->get('name');
        $level->status           = $request->get('status');

        try {
            $level->save();
            return response()->json(['id' => $level->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir o novo nível no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Recupera um nível pelo seu id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $level = Level::where('id', (int)$id)->get();
        if ($level->isEmpty()) {
            return response()->json(['message' => 'Nível não encontrado'], 404);
        }
        return response()->json(['data' => $level], 200);
    }

    /**
     * Atualiza os dados do nível.
     *
     * @param  int  $id
     *
     * @bodyParam name string required Nome do nível
     * @bodyParam name string required Nome do nível
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $enumValues = new DBServices();
        $enumValues = $enumValues->getEnumValues('users', 'status');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'name'             => 'required|string|min:2',
            'status'           => "required|string{$enumValues}",
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $level = Level::find((int)$id);
        if (!$level) {
            return response()->json(['message' => 'Nível não encontrado'], 404);
        }

        $level->name             = $request->get('name');
        $level->status           = $request->get('status');

        try {
            $level->save();
            return response()->json(['id' => $level->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o nível no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Remove um nível da base de dados.
     *
     * @param int $id ID do nível
     */
    public function destroy($id)
    {
        // !!! ATENÇÃO: É PRECISO CONFIRMAR AS REGRAS DE NEGÓCIO ANTES DE REALIZAR QUALQUER EXCLUSÃO !!!
        $level = Level::find((int)$id);

        if (!$level) {
            return response()->json(['message' => 'Nível não encontrado'], 404);
        }

        try {
            $level->delete();
            return response()->json(['id' => $level->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o nível do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }
}
