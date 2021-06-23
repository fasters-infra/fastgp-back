<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequiredFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FieldsRequiredController extends Controller
{
    public function index(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'company_id'  => 'required|int|min:1',
            'type'        => 'required|int|min:1',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $fields = RequiredFields::where('company_id', $request->company_id)->where('type', $request->type)->get();

        if (empty($fields[0])) {
            return response()->json(['message' => 'Codigo da empresa inválido'], 404);
        }

        return response()->json($fields, 200);
    }

    public function store(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'company_id'  => 'required|int|min:1',
            'type'        => 'required|int|min:1',
            'name'        => 'required|string|min:1',
            'visible'     => 'required|bool',
            'required'    => 'required|bool',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        if ($request->visible === false && $request->required === true) {
            return response()->json(['message', 'Campos obrigatórios não podem ter a visibilidade oculta!']);
        }

        $fields = RequiredFields::create([

            'company_id' => $request->company_id,
            'type'       => $request->type,
            'name'       => $request->name,
            'visible'    => $request->visible,
            'required'   => $request->required,

        ]);

        return response()->json($fields, 200);
    }

    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'company_id'  => 'required|int|min:1',
            'type'        => 'required|int|min:1',
            'name'        => 'required|string|min:1',
            'visible'     => 'required|bool',
            'required'    => 'required|bool',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        if ($request->visible === false && $request->required === true) {
            return response()->json(['message', 'Campos obrigatórios não podem ter a visibilidade oculta!']);
        }

        $field = RequiredFields::find((int)$id);
        if (!$field) {
            return response()->json(['message' => 'Campo não encontrado'], 404);
        }

        $field->company_id = $request->input('company_id');
        $field->type       = $request->input('type');
        $field->name       = $request->input('name');
        $field->visible    = $request->input('visible');
        $field->required   = $request->input('required');

        try {
            $field->save();
            return response()->json(['message' => 'Campo atualizado com sucesso'], 200);
        } catch (\Throwable $th) {
            $response = [
                "message" => "Ops! algo deu errado ao atualizar o campo!"
            ];

            return response()->json([$response, 200]);
        }
    }

    public function destroy($id)
    {
        $field = RequiredFields::find((int) $id);

        try {
            $field->delete();
            return response()->json(["message" => "Campo excluido com sucesso!"]);
        } catch (\Throwable $th) {
            $response = [
                'message' => 'Ops! algo deu errado ao excluir o campo'
            ];
            return response()->json($response, 500);
        }
    }
}
