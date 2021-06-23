<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FieldValidatorController extends Controller
{
    public function isValidFields(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [

                'cpf'    => 'nullable||unique:users,cpf,NULL,id,deleted_at,NULL', // campos a serem validados, unique:tabela onde acontecera a busca
                'cnpj'   => 'nullable||unique:users,cnpj,NULL,id,deleted_at,NULL',
                'email'  => 'nullable||unique:users,email,NULL,id,deleted_at,NULL',

            ],
            [
                'unique' => 'O campo :attribute ja existe!', // resposta da consulta
            ],
            [
                'cpf'    => 'CPF', // renomeia o atributo em questão
                'cnpj'   => 'CNPJ',
                'email'  => 'e-mail',
            ]
        );
        if ($validation->fails()) {
            return response()->json($validation->getMessageBag(), 400);
        }

        return response()->json([

            'validate' => 'success',

        ]);
    }
}
