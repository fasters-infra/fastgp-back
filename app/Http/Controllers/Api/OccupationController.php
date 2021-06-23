<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DBServices;

use App\Models\Occupation;
use Validator;

class OccupationController extends Controller
{
    /**
     * Lista os cargos
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

        $occupations = Occupation::where('name', 'like', "%$search%")
            ->where('status', 'like', "%$status%")
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $total = Occupation::where('name', 'like', "%$search%")
            ->where('status', 'like', "%$status%")
            ->count();

        $response = [
            "data"     => $occupations,
            "total"    => $total
        ];

        return response()->json($response);
    }

    /**
     * Inclui um novo cargo.
     *
     * @bodyParam name string required Nome do cargo
     * @bodyParam name status required Status do cargo
     * @bodyParam name description required Status do cargo
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $enumValues = new DBServices();
        $enumValues = $enumValues->getEnumValues('occupations', 'status');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'name'        => 'required|string|min:2',
            'skills' => 'required|string|min:2',
            'responsabilities' => 'required|string|min:2',
            'status'      => "required|string{$enumValues}",
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $occupation                   = new Occupation();
        $occupation->name             = $request->get('name');
        $occupation->skills           = $request->get('skills');
        $occupation->responsabilities = $request->get('responsabilities');
        $occupation->status           = $request->get('status');

        try {
            $occupation->save();
            return response()->json(['id' => $occupation->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir o novo cargo no banco de dados'
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
        $occupation = Occupation::where('id', (int)$id)->get();
        if ($occupation->isEmpty()) {
            return response()->json(['message' => 'Cargo não encontrado'], 404);
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
        $enumValues = new DBServices();
        $enumValues = $enumValues->getEnumValues('users', 'status');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'name'             => 'required|string|min:2',
            'skills'           => 'required|string|min:2',
            'responsabilities' => 'required|string|min:2',
            'status'           => "required|string{$enumValues}",
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $occupation = Occupation::find((int)$id);
        if (!$occupation) {
            return response()->json(['message' => 'Cargo não encontrado'], 404);
        }

        $occupation->name             = $request->get('name');
        $occupation->skills           = $request->get('skills');
        $occupation->responsabilities = $request->get('responsabilities');
        $occupation->status           = $request->get('status');

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
     * Remove um cargo da base de dados.
     *
     * @param int  $id ID do cargo
     */
    public function destroy($id)
    {
        // !!! ATENÇÃO: É PRECISO CONFIRMAR AS REGRAS DE NEGÓCIO ANTES DE REALIZAR QUALQUER EXCLUSÃO !!!
        $occupation = Occupation::find((int)$id);

        if (!$occupation) {
            return response()->json(['message' => 'Cargo não encontrado'], 404);
        }

        try {
            $occupation->delete();
            return response()->json(['id' => $occupation->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o cargo do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }
}
