<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    /**
     * Mostra todas as sessões
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:id,order',
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

        $sessions = Session::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('title', 'like', $search);
        })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();
        $recordsFiltered = Session::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('title', 'like', $search);
        })->count();
        $response = [
            "data"     => $sessions,
            "total"    => Session::count(),
            "filtered" => $recordsFiltered
        ];
        return response()->json($response);
    }

    /**
     * Cria uma sessão
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title'      => 'required',
            'order'      => 'required|int',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $data = $request->all();
        $session = Session::create($data);

        if ($session) {
            return response()->json(["message" => "Sessão criada com sucesso"]);
        } else {
            return response()->json(["message" => "Erro ao criar sessão"], 400);
        }
    }

    /**
     * Mostra todas as informações de uma sessão a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:sessions',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $session = Session::find($id);

        return response()->json($session);
    }

    /**
     * Atualiza uma sessão a partir do id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|int|exists:sessions',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $session = Session::find($id);
        $session->title = $request->get("title", $session->title);
        $session->order = $request->get("order", $session->order);
        $session->color = $request->get("color", $session->color);
        $session->save();

        return response()->json($session);
    }

    /**
     * Apaga uma sessão a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:sessions',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $session = Session::find($id);
        $session->delete();

        return response()->json(['message' => "Sessão deletada com sucesso"]);
    }
}
