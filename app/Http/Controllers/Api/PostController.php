<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Função responsável por realizar a listagem das postagens.
     *
     * @bodyParam page int required A página solicitada (A contagem de páginas é iniciada no número zero). Exemplo: 4
     * @bodyParam length int required A quantidade de registros a serem retornados. Exemplo: 50
     * @bodyParam search string String com busca realizada pelo usuário no sistema. Exemplo: Bruno
     * @bodyParam order_by string required Define qual coluna será usada como ordenação. Exemplo: text
     * @bodyParam order_dir string required Define a ordenação da listagem: asc/desc. Exemplo: desc
     *
     * @return object
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:id,text',
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

        $posts = Post::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('text', 'like', $search);
        })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $recordsFiltered = Post::where(function ($query) use ($search) {
            $search = "%" . $search . "%";
            return $query->where('text', 'like', $search);
        })->count();

        $response = [
            "data"     => $posts,
            "total"    => Post::count(),
            "filtered" => $recordsFiltered
        ];

        return response()->json($response);
    }

    /**
     * Cria uma postagem no banco de dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }
        $data = $request->all();
        if ($request->hasFile('image')) {
            $storagePath = Storage::disk('s3')->put("post_images", $request->file("image"), 'public');
            $fullUrl = Storage::disk('s3')->url($storagePath);
            $data = array_merge($data, ["url_image" => $fullUrl]);
        }
        $post = Post::create($data);
        if($post){
            return response()->json(["message" => "Postagem criada com sucesso"]);
        }else{
            return response()->json(["message" => "Erro ao criar postagem"], 400);
        }
    }

    /**
     * Retorna uma única postagem
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);

        if ($post == null) {
            return response()->json(["message" => "Postagem não encontrada"], 400);
        } else {
            return response()->json($post);//
        }
    }

    /**
     * Atualiza uma postagem.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @bodyParam text string required texto da postagem. Exemplo: Novo curso publicado
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'           => 'required|int|exists:posts',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $post = Post::find($id);

        if(!$post) return response()->json(["message" => "Postagem não encontrada"], 400);

        $data = $request->all();
        if ($request->hasFile('image')) {
            $storagePath = Storage::disk('s3')->put("post_images", $request->file("image"), 'public');
            $fullUrl = Storage::disk('s3')->url($storagePath);
            $data = array_merge($data, ["url_image" => $fullUrl]);
        }

        $post->text = $data['text'] ?? $post->text;
        $post->url_image = $data['url_image'] ?? $post->url_image;

        if ($post->save()) {
            return response()->json($post);
        } else {
            return response()->json(["message" => "Falha ao salvar dados da postagem no banco de dados", 500]);
        }
    }

    /**
     * Remove uma postagem do banco de dados. Método não implementado ainda.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        if(!$post) return response()->json(["message" => "Postagem não encontrada"], 400);

        if ($post->delete()) {
            return response()->json(["message" => "Postagem deletada com sucesso"]);
        } else {
            return response()->json(["message" => "Falha ao salvar dados da postagem no banco de dados", 500]);
        }
    }
}
