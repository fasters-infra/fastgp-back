<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    /**
     * Mostra todos os times
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:id',
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

        $teams = Team::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('name', 'like', $search);
        })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();
        $recordsFiltered = Team::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('name', 'like', $search);
        })->count();
        $response = [
            "data"     => $teams,
            "total"    => Team::count(),
            "filtered" => $recordsFiltered
        ];
        return response()->json($response);
    }

    /**
     * Cria um time
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'      => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $data = $request->all();
        $team = Team::create($data);

        if ($team) {
            return response()->json(["message" => "Time criado com sucesso", "team" => $team]);
        } else {
            return response()->json(["message" => "Erro ao criar time"], 400);
        }
    }

    /**
     * Mostra todas as informações de um time incluindo os membros a partir do id do time
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:teams',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $team = Team::find($id)->with('members')->find($id);

        return response()->json($team);
    }

    /**
     * Atualiza um time a partir do id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'    => 'required|int|exists:teams',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $team = Team::find($id);
        $team->name = $request->get("name", $team->name);
        $team->save();

        return response()->json($team);
    }

    /**
     * Apaga um time a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:teams',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $team = Team::find($id);
        $team->delete();

        return response()->json(['message' => "Time deletado com sucesso"]);
    }
}
