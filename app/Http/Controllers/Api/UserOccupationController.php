<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserOccupation;
use App\Services\DBServices;
use Validator;
use DB;

class UserOccupationController extends Controller
{
    /**
     * Função responsável por realizar a listagem dos cargos de usuários.
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
            'order_by'  => 'required|in:id,name,occupation_name,level_name,user_name,created_at',
            'order_dir' => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $page     = (int) $request->get("page");
        $length   = (int) $request->get("length");
        $search   = $request->get("search");
        $orderBy  = $request->get("order_by");
        $orderDir = $request->get("order_dir");

        $occupations = DB::table('user_occupations')
            ->join('occupation_levels', 'user_occupations.occupation_levels_id', '=', 'occupation_levels.id')
            ->join('occupations', 'occupation_levels.occupation_id', '=', 'occupations.id')
            ->join('levels', 'occupation_levels.level_id', '=', 'levels.id')
            ->join('users', 'user_occupations.user_id', '=', 'users.id')
            ->select(
                'occupation_levels.*',
                'occupations.name AS occupation_name',
                'levels.name AS level_name',
                'users.name AS user_name',
                'users.cpf'
            )
            ->where('users.name', 'like', "%$search%")
            ->orWhere('occupations.name', 'like', "%$search%")
            ->orWhere('levels.name', 'like', "%$search%")
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $total = DB::table('user_occupations')
            ->join('occupation_levels', 'user_occupations.occupation_levels_id', '=', 'occupation_levels.id')
            ->join('occupations', 'occupation_levels.occupation_id', '=', 'occupations.id')
            ->join('levels', 'occupation_levels.level_id', '=', 'levels.id')
            ->join('users', 'user_occupations.user_id', '=', 'users.id')
            ->where('users.name', 'like', "%$search%")
            ->orWhere('occupations.name', 'like', "%$search%")
            ->orWhere('levels.name', 'like', "%$search%")
            ->count();

        $response = [
            "data"     => $occupations,
            "total"    => $total
        ];

        return response()->json($response);
    }

    /**
     * Insere um novo cargo para o usuário.
     *
     * @bodyParam user_id int required ID do usuário
     * @bodyParam occupation_levels_id int required ID do cargo-nívl
     * @bodyParam start date required Data de início no cargo
     * @bodyParam end date Data de fim no cargo
     * @bodyParam observations string Observações referente à nova posição
     * @bodyParam workload int required Carga horária em horas
     * @bodyParam workload_period enum required Frequência de trabalho.
     * @bodyParam hour_value float required Valor da hora trabalhada
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $enumValues = new DBServices();
        $enumValues = $enumValues->getEnumValues('user_occupations', 'workload_period');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'user_id'              => 'required|integer',
            'occupation_levels_id' => 'required|integer',
            'start'                => 'required|date|date_format:Y-m-d',
            'end'                  => 'date|date_format:Y-m-d',
            'workload'             => 'required|integer',
            'workload_period'      => "required|string{$enumValues}",
            'hour_value'           => 'required|numeric|between:0,9999.99'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $occupation                       = new UserOccupation();
        $occupation->user_id              = $request->get('user_id');
        $occupation->occupation_levels_id = $request->get('occupation_levels_id');
        $occupation->start                = $request->get('start');
        $occupation->end                  = $request->get('end');
        $occupation->observations         = $request->get('observations');
        $occupation->workload             = $request->get('workload');
        $occupation->workload_period      = $request->get('workload_period');
        $occupation->hour_value           = $request->get('hour_value');

        try {
            $occupation->save();
            return response()->json(['id' => $occupation->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir o registro no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Recupera um cargo do usuário pelo seu id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $occupation = UserOccupation::where('id', (int)$id)->get();
        if ($occupation->isEmpty()) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }
        return response()->json(['data' => $occupation], 200);
    }

    /**
     * Atualiza um cargo do usuário para o usuário.
     *
     * @param  int  $id
     *
     * @bodyParam user_id int required ID do usuário
     * @bodyParam occupation_levels_id int required ID do cargo-nívl
     * @bodyParam start date required Data de início no cargo
     * @bodyParam end date Data de fim no cargo
     * @bodyParam workload int required Carga horária em horas
     * @bodyParam workload_period enum required Frequência de trabalho.
     * @bodyParam hour_value float required Valor da hora trabalhada
     * @bodyParam observations string Observações referente à nova posição
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'user_id'              => 'required|integer',
            'occupation_levels_id' => 'required|integer',
            'start'                => 'required|date|date_format:Y-m-d',
            'end'                  => 'date|date_format:Y-m-d'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $occupation = UserOccupation::find((int)$id);
        if (!$occupation) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        $occupation->user_id              = $request->get('user_id');
        $occupation->occupation_levels_id = $request->get('occupation_levels_id');
        $occupation->start                = $request->get('start');
        $occupation->end                  = $request->get('end');
        $occupation->observations         = $request->get('observations');
        $occupation->workload             = $request->get('workload');
        $occupation->workload_period      = $request->get('workload_period');
        $occupation->hour_value           = $request->get('hour_value');

        try {
            $occupation->save();
            return response()->json(['id' => $occupation->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o registro no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Remove um cargo do usuário da base de dados.
     *
     * @param int  $id ID do cargo do usuário
     */
    public function destroy($id)
    {
        $occupation = UserOccupation::find((int)$id);

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
