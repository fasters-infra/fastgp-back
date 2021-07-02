<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

use App\Models\SocialMedia;
use App\Models\UserSocialMedia;
use App\Models\User;
use DB;
class SocialMediaController extends Controller
{
    /**
     * Busca as redes sociais.
     *
     * @bodyParam page int required A página solicitada (A contagem de páginas é iniciada no número zero). Exemplo: 4
     * @bodyParam length int required A quantidade de registros a serem retornados. Exemplo: 50
     * @bodyParam search string String com busca realizada pelo usuário no sistema. Exemplo: Bruno
     * @bodyParam order_by string required Define qual coluna será usada como ordenação. Exemplo: name
     * @bodyParam order_dir string required Define a ordenação da listagem: asc/desc. Exemplo: desc
     *
     * @return object
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:name',
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

        $medias = SocialMedia::where('name', 'LIKE', "%{$search}%")
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $total = SocialMedia::where('name', 'LIKE', "%{$search}%")
            ->count();

        $response = [
            "data"     => $medias,
            "total"    => $total
        ];

        return response()->json($response);
    }

    /**
     * Inclui uma nova rede social
     *
     * @bodyParam name string required Nome da rede social
     * @bodyParam url string required URL da rede social
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'  => 'required|string|min:2',
            'url' => 'required|string|url',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $social = new SocialMedia();
        $social->name = $request->get('name');
        $social->url = $request->get('url');

        try {
            $social->save();
            return response()->json(['id' => $social->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir o novo registro no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Recupera um registro de rede social dado o seu id
     *
     * @param $id int Identificador da rede social
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $smtp = SocialMedia::where('id', (int)$id)->get();
        if ($smtp->isEmpty()) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }
        return response()->json(['data' => $smtp], 200);
    }

    /**
     * Atualiza uma rede social
     *
     * @bodyParam name string required Nome da rede social
     * @bodyParam url string required URL da rede social
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name'  => 'required|string|min:2',
            'url' => 'required|string|url',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $social = SocialMedia::find((int)$id);
        if (!$social) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        $social->name = $request->get('name');
        $social->url = $request->get('url');

        try {
            $social->save();
            return response()->json(['id' => $social->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o registro do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Deleta um registro de redes sociais do banco de dados
     *
     * @param $id int Identificador do registro
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $social = SocialMedia::find((int)$id);
        if (!$social) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        try {
            $social->delete();
            return response()->json(['id' => $social->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o registro do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Insere uma rede social para um usuário
     *
     * @bodyParam id_social int required ID da rede social
     * @bodyParam id_user int required ID do usuário
     * @bodyParam url string required URL da rede social do usuário
     *
     * @return \Illuminate\Http\Response
     */
    public function setUserSocialMedia(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'user_id'   => 'required|int',
            'social_id' => 'required|int',
            'url'       => 'required|string|url'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $user_id   = (int)$request->get('user_id');
        $social_id = (int)$request->get('social_id');
        $url       = addslashes($request->get('url'));

        try {
            $social = UserSocialMedia::where('user_id', '=', $user_id)
                ->where('social_id', '=', $social_id)
                ->get();

            if ($social->isEmpty()) {
                $social            = new UserSocialMedia();
                $social->user_id   = $user_id;
                $social->social_id = $social_id;
                $social->url       = $url;
                $social->save();

                $id = $social->id;
            } else {
                UserSocialMedia::where('user_id', $user_id)
                    ->where('social_id', $social_id)
                    ->update(['url' => $url]);

                $id = $social[0]->id;
            }

            return response()->json(['id' => $id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir/atualizar o registro do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Deleta uma rede social de um usuário
     *
     * @bodyParam id_social int required ID da rede social
     * @bodyParam id_user int required ID do usuário
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteUserSocialMedia($id)
    {
        $social = UserSocialMedia::find((int)$id);
        if (!$social) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }

        try {
            $social->delete();
            return response()->json(['id' => $social->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o registro do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }
}
