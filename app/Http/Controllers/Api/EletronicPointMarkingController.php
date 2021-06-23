<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EletronicPointMarking;
use App\Models\EletronicPointProfile;
use App\Models\User;
use App\Repositories\EletronicPointMarkingRepository;
use App\Repositories\EletronicPointProfileRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EletronicPointMarkingController extends Controller
{
    private $eletronicPointMarkingRepository;

    function __construct()
    {
        $this->eletronicPointMarkingRepository = new EletronicPointMarkingRepository(new EletronicPointMarking());
    }

    public function mark()
    {
        $user = auth('api')->user();

        $markation = $this->eletronicPointMarkingRepository->mark($user->id, new  UserRepository(new User()));

        if (empty($markation)) {
            return response()->json(['message' => 'Você já efetuou 4 marcações hoje'], 400);
        }

        return response()->json(['message' => 'Marcação efetuada com sucesso']);
    }

    public function justify(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|exists:eletronic_point_markings,id',
            'justified_by'  => 'required|exists:users,id',
            'justification' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->getMessageBag(), 400);
        }

        $this->eletronicPointMarkingRepository->justify($id, $request->get('justified_by'), $request->get('justification'));

        return response()->json(['message' => 'Marcação justificada com sucesso']);
    }

    public function period(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'start_date'    => 'required|date',
            'end_date'      => 'required|date',
            'user_id'       => 'required|exists:users,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->getMessageBag(), 400);
        }

        $periodMarkation = $this->eletronicPointMarkingRepository->period($request->get('start_date'), $request->get('end_date'), (int) $request->get('user_id'));

        return response()->json([$periodMarkation]);
    }
}
