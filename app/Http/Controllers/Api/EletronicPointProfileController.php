<?php

namespace App\Http\Controllers\Api;

use App\DataObject\EletronicPointProfile as DataObjectEletronicPointProfile;
use App\DataObject\SeachIndex;
use App\Http\Controllers\Controller;
use App\Models\EletronicPointProfile;
use App\Repositories\EletronicPointProfileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EletronicPointProfileController extends Controller
{
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:id,order',
            'order_dir' => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->getMessageBag(), 400);
        }

        $searchDataObject = new SeachIndex(
            (int) $request->get("page"),
            (int) $request->get("length"),
            (string) $request->get("order_by"),
            (string) $request->get("order_dir")
        );

        $searchDataObject->setSearch((string) $request->get('search'));

        $eletronicPointProfileRepository = new EletronicPointProfileRepository(new EletronicPointProfile());

        $response = [
            "data"     => $eletronicPointProfileRepository->filter($searchDataObject),
            "total"    => $eletronicPointProfileRepository->count(),
            "filtered" => $eletronicPointProfileRepository->filtered($searchDataObject)
        ];

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'title'                 => 'required',
            'entry_time'            => 'required',
            'break_time'            => 'required',
            'interval_return_time'  => 'required',
            'departure_time'        => 'required',
            'tolerance'             => 'nullable|integer'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->getMessageBag(), 400);
        }

        $eletronicPointProfileDataObject = new DataObjectEletronicPointProfile(
            (string) $request->get('title'),
            (string) $request->get('entry_time'),
            (string) $request->get('break_time'),
            (string) $request->get('interval_return_time'),
            (string) $request->get('departure_time')
        );

        $eletronicPointProfileDataObject->setTolerance((int) $request->get('tolerance'));

        $eletronicPointProfileRepository = new EletronicPointProfileRepository(new EletronicPointProfile());

        $eletronicPointProfile = $eletronicPointProfileRepository->store($eletronicPointProfileDataObject);

        if(empty($eletronicPointProfile)){
            return response()->json(['message' => 'Erro ao criar perfil de ponto eletrônico.'], 400);
        }

        return response()->json($eletronicPointProfile);
    }

    public function show($id)
    {
        $eletronicPointProfileRepository = new EletronicPointProfileRepository(new EletronicPointProfile());

        $eletronicPointProfile = $eletronicPointProfileRepository->find($id);

        if(empty($eletronicPointProfile)){
            return response()->json(['message' => 'Perfil de ponto eletrônico não encontrado'], 400);
        }

        return response()->json($eletronicPointProfile);
    }

    public function update(Request $request, $id)
    {
        $eletronicPointProfileRepository = new EletronicPointProfileRepository(new EletronicPointProfile());

        $eletronicPointProfile = $eletronicPointProfileRepository->find($id);

        if(empty($eletronicPointProfile)){
            return response()->json(['message' => 'Perfil de ponto eletrônico não encontrado'], 400);
        }

        $eletronicPointProfileDataObject = new DataObjectEletronicPointProfile(
            ($request->get('title') ?? $eletronicPointProfile->title),
            ($request->get('entry_time') ?? $eletronicPointProfile->entry_time),
            ($request->get('break_time') ?? $eletronicPointProfile->break_time),
            ($request->get('interval_return_time') ?? $eletronicPointProfile->interval_return_time),
            ($request->get('departure_time') ?? $eletronicPointProfile->departure_time)
        );

        $eletronicPointProfileDataObject->setTolerance($request->get('tolerance') ?? $eletronicPointProfile->tolerance);

        $eletronicPointProfile = $eletronicPointProfileRepository->update($eletronicPointProfileDataObject, $id);

        return response()->json($eletronicPointProfile);
    }

    public function destroy($id)
    {
        $eletronicPointProfileRepository = new EletronicPointProfileRepository(new EletronicPointProfile());

        $eletronicPointProfile = $eletronicPointProfileRepository->find($id);

        if(empty($eletronicPointProfile)){
            return response()->json(['message' => 'Perfil de ponto eletrônico não encontrado'], 400);
        }

        $eletronicPointProfileRepository->delete($id);

        return response()->json(['message' => 'Perfil de ponto eletrônico deletedo']);
    }
}
