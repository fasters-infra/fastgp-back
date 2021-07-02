<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legend;
use App\Models\PdiAppraiser;
use App\Models\Rating;
use App\Models\RatingLegend;
use App\Models\RatingResponse;
use App\Models\RatingSession;
use App\Models\SubSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RatingResponseController extends Controller
{
    /**
     * Mostra a lista de avaliações e respostas
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

        $rating_response = RatingResponse::orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $response = [
            "data"     => $rating_response,
            "total"    => RatingResponse::count(),
        ];
        return response()->json($response);
    }

    /**
     * Associa uma avaliação com uma resposta utilizando a legenda
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'rating_id'                     => 'required|exists:ratings,id',
            'responses'                     => 'required|array|min:1',
            'responses.*.sub_session_id'    => 'required|exists:sub_sessions,id',
            'responses.*.legend_id'         => 'required|exists:legends,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $user = auth('api')->user();
        $rating = Rating::find($request->get('rating_id'));
        $user_rating = User::find($rating->user_id);

        $pdiAppraisers = PdiAppraiser::where('rating_id', $rating->id)->get();

        $isAppriover = false;
        foreach ($pdiAppraisers as $pdiAppraiser) {
            if ($pdiAppraiser->appraiser_id == $user->id) {
                $isAppriover = true;
                break;
            }
        }

        if (!$isAppriover) {
            return response()->json(["message" => "Usuário não é o avaliador desse PDI"], 400);
        }

        $countReponses = RatingResponse::where('rating_id', $rating->id)->count();
        if ($countReponses > 0) {
            return response()->json(["message" => "Não é possivel avalidar um PDI já avaliado antes"], 400);
        }

        foreach ($request->get('responses') as $response) {
            if (!$this->validateResponse($response['sub_session_id'], $request->get('rating_id'))) {
                return response()->json(["message" => "Uma das respostas enviada já foi registrada"], 400);
            }
            if (!$this->validateSubSession($response['sub_session_id'], $request->get('rating_id'))) {
                return response()->json(["message" => "Uma das sub-sessões enviada nas respostas não está atrelada a uma sessão atrelada a essa avaliação"], 400);
            }
            if (!$this->validateLegend($response['legend_id'], $request->get('rating_id'))) {
                return response()->json(["message" => "Uma das legendas enviada nas respostas não está associada à essa avaliação"], 400);
            }
        }

        $final_grade = 0;
        $count = 0;

        $user_rating->positive_point = $request->get('positive_point', $user_rating->positive_point);
        $user_rating->negative_point = $request->get('negative_point', $user_rating->negative_point);

        foreach ($request->get('responses') as $response) {
            $legend = Legend::find($response['legend_id']);

            $final_grade += $legend->value;
            $count++;

            RatingResponse::create([
                'rating_id'         => $request->get('rating_id'),
                'sub_session_id'    => $response['sub_session_id'],
                'legend_id'         => $legend->id,
            ]);
        }

        $final_grade = $final_grade / $count;

        $user_rating->final_grade = $final_grade;
        $user_rating->save();

        return response()->json(["message" => "Respostas armazenadas com sucesso"]);
    }

    /**
     * Mostra apenas uma resposta de avaliação a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:rating_responses',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_response = RatingResponse::with('rating')->with('sub_session')->with('legend')->find($id);

        return response()->json($rating_response);
    }

    /**
     * Atualiza uma resposta de uma avaliação
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'                => 'required|int|exists:rating_responses',
            'rating_id'         => 'int|exists:ratings,id',
            'sub_session_id'    => 'int|exists:sub_sessions,id',
            'legend_id'         => 'int|exists:legends,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_response = RatingResponse::find($id);
        $rating_id = $request->get("rating_id", $rating_response->rating_id);
        $rating_response->rating_id = $rating_id;

        if (!is_null($request->get("sub_session_id"))) {
            if (!$this->validateSubSession($request->get('sub_session_id'), $rating_id)) {
                return response()->json(["message" => "A sub-sessão não está atrelada a uma sessão atrelada a essa avaliação"], 400);
            }

            $rating_response->sub_session_id = $request->get("sub_session_id");
        }
        if (!is_null($request->get("legend_id"))) {
            if (!$this->validateLegend($request->get('legend_id'), $rating_id)) {
                return response()->json(["message" => "A legenda não está associada à essa avaliação"], 400);
            }

            $rating_response->legend_id = $request->get("legend_id");
        }

        $rating_response->save();

        return response()->json($rating_response);
    }

    /**
     * Apaga uma resposta de uma avaliação
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:rating_responses',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating_response = RatingResponse::find($id);
        $rating_response->delete();

        return response()->json(['message' => "Resposta deletada com sucesso"]);
    }

    /**
     * Função para validar se a resposta recebida ja possuí uma legenda atribuida a ela
     */
    private function validateResponse($sub_session_id, $rating_id)
    {
        $rating_response_count = RatingResponse::where('rating_id', $rating_id)
            ->where('sub_session_id', $sub_session_id)
            ->count();

        if ($rating_response_count > 0) {
            return false;
        }

        return true;
    }


    /**
     * Função para validar se a sub-sessão recebida tem ligação com a avaliação recebida
     */
    private function validateSubSession($sub_session_id, $rating_id)
    {
        $sub_session = SubSession::find($sub_session_id);
        $rating_session_count = RatingSession::where('session_id', $sub_session->session_id)
            ->where('rating_id', $rating_id)
            ->count();

        if ($rating_session_count <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Função para validar se a legenda recebida tem ligação com a avaliação recebida
     */
    private function validateLegend($legend_id, $rating_id)
    {
        $rating_legend_count = RatingLegend::where('rating_id', $rating_id)
            ->where('legend_id', $legend_id)
            ->count();

        if ($rating_legend_count <= 0) {
            return false;
        }

        return true;
    }
}
