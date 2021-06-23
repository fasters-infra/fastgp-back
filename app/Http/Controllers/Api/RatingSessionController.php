<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RatingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingSessionController extends Controller
{
    /**
     * Mostra todas as associações entre sessões e avaliações
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
        $orderBy  = $request->get("order_by");
        $orderDir = $request->get("order_dir");

        $rating_sessions = RatingSession::orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $response = [
            "data"     => $rating_sessions,
            "total"    => RatingSession::count(),
        ];
        return response()->json($response);
    }

    /**
     * Associa uma sessão com uma avaliação
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'rating_id'      => 'required|exists:ratings,id',
            'session_id'      => 'required|exists:sessions,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_session_count = RatingSession::where('rating_id', $request->get('rating_id'))
        ->where('session_id', $request->get('session_id'))
        ->count();

        if($rating_session_count > 0){
            return response()->json(["message" => "Sessão já associada à avaliação"], 400);
        }

        $data = $request->all();
        $rating_session = RatingSession::create($data);
        if ($rating_session) {
            return response()->json(["message" => "Sessão associada à avaliação com sucesso"]);
        } else {
            return response()->json(["message" => "Erro ao associar os dados"], 400);
        }
    }

    /**
     * Mostra uma associação entre sessão e avaliação
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rating_session = RatingSession::with('rating')->with('session')->find($id);
        if ($rating_session == null) {
            return response()->json(["message" => "Associação não encontrada"], 400);
        }
        return response()->json($rating_session);
    }

    /**
     * Atualiza uma associação entre sessão e avaliação
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|int|exists:rating_sessions',
            'rating_id'      => 'required|exists:ratings,id',
            'session_id'      => 'required|exists:sessions,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_session = RatingSession::find($id);
        $rating_session->rating_id = $request->get("rating_id", $rating_session->rating_id);
        $rating_session->session_id = $request->get("session_id", $rating_session->session_id);
        $rating_session->save();

        return response()->json($rating_session);
    }

    /**
     * Apaga uma associação entre sessão e avaliação
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:rating_sessions',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_session = RatingSession::find($id);
        $rating_session->delete();

        return response()->json(['message' => "Associação deletada com sucesso"]);
    }
}
