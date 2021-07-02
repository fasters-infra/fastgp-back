<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Smtp;

/**
 * SmtpController
 */
class SmtpController extends Controller
{
    /**
     * Exibe os smtps cadastrados
     *
     * @bodyParam page int required A página solicitada (A contagem de páginas é iniciada no número zero). Exemplo: 4
     * @bodyParam length int required A quantidade de registros a serem retornados. Exemplo: 50
     * @bodyParam search string String com busca realizada pelo usuário no sistema. Exemplo: João
     * @bodyParam order_by string required Define qual coluna será usada como ordenação. Exemplo: name
     * @bodyParam order_dir string required Define a ordenação da listagem: asc/desc. Exemplo: desc
     *
     * @return \Illuminate\Http\Response
     *
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:alias,created_at',
            'order_dir' => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $page     = (int) $request->get("page");
        $length   = (int) $request->get("length");
        $search   = trim(addslashes($request->get("search")));
        $orderBy  = $request->get("order_by");
        $orderDir = $request->get("order_dir");

        $smtps = Smtp::where('alias', 'LIKE', "%$search%")
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $total = Smtp::where('alias', 'LIKE', "%$search%")
            ->count();

        $response = [
            'data'     => $smtps,
            'total'    => $total
        ];

        return response()->json($response);
    }

    /**
     * Insere um novo smtp no banco de dados
     *
     * @bodyParam alias string required O nome/descrição para o stmp. Exemplo: 'Servidor de email 1'
     * @bodyParam smtp string required URL de domínio do smpt Exemplo: 'smtp.domain.com.br'
     * @bodyParam user string required Usuário de autenticação Exemplo: 'user@domain.com.br'
     * @bodyParam password string required Senha de autenticação
     * @bodyParam port int required Porta de acesso do smtp
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'alias'    => 'required|string|min:2',
            'smtp'     => 'required|string|url',
            'user'     => 'required|string',
            'password' => 'required|string',
            'port'     => 'required|int|gt:0'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $smtp           = new Smtp();
        $smtp->alias    = $request->get('alias');
        $smtp->smtp     = $request->get('smtp');
        $smtp->user     = $request->get('user');
        $smtp->password = $request->get('password');
        $smtp->port     = $request->get('port');

        try {
            $smtp->save();
            return response()->json(['id' => $smtp->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir o novo registro no banco de dados'
            ];
            return response()->json($response, 500);
        }
        //
    }

    /**
     * Recupera um registro smtp dado o seu id
     *
     * @param $id int Identificador do registro
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $smtp = Smtp::where('id', (int)$id)->get();
        if ($smtp->isEmpty()) {
            return response()->json(['message' => 'SMTP não encontrado'], 404);
        }
        return response()->json(['data' => $smtp], 200);
        //
    }

    /**
     * Atualiza um registro de smtp no banco de dados
     *
     * @param $id int Identificador do registro
     *
     * @bodyParam alias string required O nome/descrição para o stmp. Exemplo: 'Servidor de email 1'
     * @bodyParam smtp string required URL de domínio do smpt Exemplo: 'smtp.domain.com.br'
     * @bodyParam user string required Usuário de autenticação Exemplo: 'user@domain.com.br'
     * @bodyParam password string required Senha de autenticação
     * @bodyParam port int required Porta de acesso do smtp
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'alias'    => 'required|string|min:2',
            'smtp'     => 'required|string|url',
            'user'     => 'required|string',
            'password' => 'required|string',
            'port'     => 'required|int|gt:0'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $smtp = Smtp::find((int)$id);
        if (!$smtp) {
            return response()->json(['message' => 'SMTP não encontrado'], 404);
        }

        $smtp->alias    = $request->get('alias');
        $smtp->smtp     = $request->get('smtp');
        $smtp->user     = $request->get('user');
        $smtp->password = $request->get('password');
        $smtp->port     = $request->get('port');

        try {
            $smtp->save();
            return response()->json(['id' => $smtp->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o smtp no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Deleta um registro de smt do banco de dados
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $smtp = Smtp::find((int)$id);

        try {
            $smtp->delete();
            return response()->json(['id' => $smtp->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o smtp do banco de dados'
            ];
            return response()->json($response, 500);
        }
        //
    }
}
