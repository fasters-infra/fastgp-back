<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RatingLegend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingLegendController extends Controller
{
    /**
     * Mostra todas as legendas atreladas a sessões
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

        $rating_legends = RatingLegend::orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $response = [
            "data"     => $rating_legends,
            "total"    => RatingLegend::count(),
        ];
        return response()->json($response);
    }

    /**
     * Associa uma legenda a uma avaliação
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'rating_id'      => 'required|exists:ratings,id',
            'legend_id'      => 'required|exists:legends,id',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_legend_count = RatingLegend::where('rating_id', $request->get('rating_id'))
        ->where('legend_id', $request->get('legend_id'))
        ->count();

        if($rating_legend_count > 0){
            return response()->json(["message" => "Legenda já associada à avaliação"], 400);
        }

        $data = $request->all();
        $rating_legend = RatingLegend::create($data);
        if ($rating_legend) {
            return response()->json(["message" => "Legenda associada à avaliação com sucesso"]);
        } else {
            return response()->json(["message" => "Erro ao associar os dados"], 400);
        }
    }

    /**
     * Mostra os dados de uma associação entre legenda e avaliação
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rating_legend = RatingLegend::with('rating')->with('legend')->find($id);
        if ($rating_legend == null) {
            return response()->json(["message" => "Associação não encontrada"], 400);
        }
        return response()->json($rating_legend);
    }

    /**
     * Atualiza uma associação entre legenda e avaliação a partir do id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|int|exists:rating_legends',
            'rating_id'      => 'required|exists:ratings,id',
            'legend_id'      => 'required|exists:legends,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_legend = RatingLegend::find($id);
        $rating_legend->rating_id = $request->get("rating_id", $rating_legend->rating_id);
        $rating_legend->legend_id = $request->get("legend_id", $rating_legend->legend_id);
        $rating_legend->save();

        return response()->json($rating_legend);
    }

    /**
     * Apaga uma associação entre legenda e avaliação
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:rating_legends',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_legend = RatingLegend::find($id);
        $rating_legend->delete();

        return response()->json(['message' => "Associação deletada com sucesso"]);
    }
}
