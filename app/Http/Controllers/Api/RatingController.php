<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Legend;
use App\Models\PdiAppraiser;
use App\Models\Rating;
use App\Models\RatingLegend;
use App\Models\RatingResponse;
use App\Models\RatingSession;
use App\Models\Session;
use App\Models\SubSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    /**
     * Mostra todas as avaliações
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:id,schedule',
            'order_dir' => 'required|in:asc,desc',
            'filter_by' => 'in:user_id',
            'search'    => [Rule::requiredIf(function () use ($request) {
                $exist_filter = $request->get('filter_by', '');
                return $exist_filter != '';
            }), 'int'],
        ]);
        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }
        $page     = (int) $request->get('page');
        $length   = (int) $request->get('length');
        $orderBy  = $request->get('order_by');
        $orderDir = $request->get('order_dir');
        $filterBy = $request->get('filter_by');
        $search = $request->get('search');


        $ratings = Rating::orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length);

        if (isset($filterBy)) {
            $ratings = $ratings->where($filterBy, $search);
        }

        $ratings = $ratings->get();

        $response = [
            'data'     => $ratings,
            'total'    => Rating::count(),
        ];
        return response()->json($response);
    }

    /**
     * Mostra todas as referentes a um aprovador
     *
     * @return \Illuminate\Http\Response
     */
    public function ratingByAppriser(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'          => 'required|int|min:0',
            'length'        => 'required|int|min:1|max:100',
            'order_by'      => 'required|in:id,schedule',
            'order_dir'     => 'required|in:asc,desc',
            'appraiser_id'  => 'required|exists:users,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }
        $page     = (int) $request->get('page');
        $length   = (int) $request->get('length');
        $orderBy  = $request->get('order_by');
        $orderDir = $request->get('order_dir');

        $appraiser_id = $request->get('appraiser_id');
        $ratings = Rating::select('ratings.*', 'pdi_appraisers.appraiser_id')
            ->join('pdi_appraisers', 'ratings.id', '=', 'pdi_appraisers.rating_id')
            ->where('pdi_appraisers.appraiser_id', $appraiser_id)
            ->where(DB::raw('(SELECT COUNT(*) FROM rating_responses WHERE rating_responses.rating_id = ratings.id)'), '=', 0)
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)
            ->get();

        $response = [
            'data'     => $ratings,
            'total'    => Rating::count(),
            "filtered" => count($ratings)
        ];
        return response()->json($response);
    }

    /**
     * Cria uma avaliação para um usuário
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'appraisers.*'      => 'required|exists:users,id',
            'title'             => 'required',
            'legends.*.id'      => 'exists:legends,id',
            'sessions.*.id'     => 'exists:sessions,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $data = $request->all();
        $rating = Rating::create($data);

        $appraiser_ids = $request->get('appraisers');
        foreach ($appraiser_ids as $appraiser_id) {
            PdiAppraiser::create([
                "appraiser_id" => $appraiser_id,
                "rating_id" => $rating->id
            ]);
        }

        $legends = $request->get('legends');
        foreach ($legends as $legend) {
            if (empty($legend['id'])) {
                $legend = Legend::create($legend);
            }

            $legend_id = empty($legend->id) ? $legend['id'] : $legend->id;

            RatingLegend::create([
                'rating_id' => $rating->id,
                'legend_id' => $legend_id
            ]);
        }

        $sessions = $request->get('sessions');
        foreach ($sessions as $session) {
            if (empty($session['id'])) {
                $session_create = Session::create($session);
            }

            $session_id = empty($session_create->id) ? $session['id'] : $session_create->id;

            RatingSession::create([
                'rating_id' => $rating->id,
                'session_id' => $session_id
            ]);

            $sub_sessions = $session['sub_sessions'] ?? [];
            foreach ($sub_sessions as $sub_session) {
                SubSession::create(array_merge($sub_session, ['session_id' => $session_id]));
            }
        }

        if ($rating) {
            return response()->json(['message' => 'Avaliação criada com sucesso', 'rating' => $rating]);
        } else {
            return response()->json(['message' => 'Erro ao criar avaliação'], 400);
        }
    }

    /**
     * Mostra uma avaliação com todas as informações, inclusive legendas e sessões atreladas a ela a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rating = Rating::with('user')
            ->with('appraisers')
            ->with('legends')
            ->with('sessions')
            ->find($id);
        if ($rating == null) {
            return response()->json(['message' => 'Avaliação não encontrada'], 400);
        }
        return response()->json($rating);
    }

    /**
     * Atualiza uma avaliação a partir do id
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'                => 'required|int|exists:ratings',
            'user_id'           => 'int|exists:users,id',
            'appraisers.*'      => 'int|exists:users,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating = Rating::find($id);
        $rating->user_id = $request->get('user_id', $rating->user_id);
        $rating->schedule = $request->get('schedule', $rating->schedule);
        $rating->title = $request->get('title', $rating->title);
        $rating->save();

        $appraisers = $request->get('appraisers', []);
        if (count($appraisers) > 0) {
            PdiAppraiser::where('rating_id', $rating->id)->delete();
            foreach ($appraisers as $appraiser_id) {
                PdiAppraiser::create([
                    "appraiser_id" => $appraiser_id,
                    "rating_id" => $rating->id
                ]);
            }
        }

        return response()->json($rating);
    }

    /**
     * Apaga uma avaliação a partir do id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:ratings',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $rating = Rating::find($id);
        $rating->delete();

        return response()->json(['message' => 'Avaliação deletada com sucesso']);
    }
}
