<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LegendController extends Controller
{
    /**
     * Mostra todas as legendas
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:id,value',
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

        $legends = Legend::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('label', 'like', $search);
        })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();
        $recordsFiltered = Legend::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('label', 'like', $search);
        })->count();
        $response = [
            "data"     => $legends,
            "total"    => Legend::count(),
            "filtered" => $recordsFiltered
        ];
        return response()->json($response);
    }

    /**
     * Cria uma legenda
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'value'      => 'required',
            'label'      => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $data = $request->all();
        $legend = Legend::create($data);

        if ($legend) {
            return response()->json(["message" => "Legenda criada com sucesso"]);
        } else {
            return response()->json(["message" => "Erro ao criar legenda"], 400);
        }
    }

    /**
     * Mostra as informações de uma legenda a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:legends',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $legend = Legend::find($id);

        return response()->json($legend);
    }

    /**
     * Atualiza uma legenda a partir do id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|int|exists:legends',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $legend = Legend::find($id);
        $legend->value = $request->get("value", $legend->value);
        $legend->label = $request->get("label", $legend->label);
        $legend->color = $request->get("color", $legend->color);
        $legend->save();

        return response()->json($legend);
    }

    /**
     * Apaga uma legenda a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:legends',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $legend = Legend::find($id);
        $legend->delete();

        return response()->json(['message' => "Legenda deletada com sucesso"]);
    }
}
