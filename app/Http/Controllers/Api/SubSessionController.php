<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubSessionController extends Controller
{
    /**
     * Mostra todas as sub-sessões atrelada a uma sessão
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

        $sub_sessions = SubSession::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('title', 'like', $search);
        })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();
        $recordsFiltered = SubSession::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('title', 'like', $search);
        })->count();
        $response = [
            "data"     => $sub_sessions,
            "total"    => SubSession::count(),
            "filtered" => $recordsFiltered
        ];
        return response()->json($response);
    }

    /**
     * Cria uma sub-sessão para uma sessão
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'session_id'      => 'required|exists:sessions,id',
            'title'      => 'required',
            'order'      => 'required|int',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $data = $request->all();
        $sub_session = SubSession::create($data);

        if ($sub_session) {
            return response()->json(["message" => "Sub-sessão criada com sucesso"]);
        } else {
            return response()->json(["message" => "Erro ao criar sub-sessão"], 400);
        }
    }

    /**
     * Mostra as informações de uma sub-sessão a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:sub_sessions',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $sub_session = SubSession::with('session')->find($id);

        return response()->json($sub_session);
    }

    /**
     * Atualiza uma sub-sessão a partir do id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|int|exists:sub_sessions',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $sub_session = SubSession::find($id);
        $sub_session->session_id = $request->get("session_id", $sub_session->session_id);
        $sub_session->title = $request->get("title", $sub_session->title);
        $sub_session->order = $request->get("order", $sub_session->order);
        $sub_session->save();

        return response()->json($sub_session);
    }

    /**
     * Apaga uma sub-sessão a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:sub_sessions',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $sub_session = SubSession::find($id);
        $sub_session->delete();

        return response()->json(['message' => "Sub-sessão deletada com sucesso"]);
    }
}
