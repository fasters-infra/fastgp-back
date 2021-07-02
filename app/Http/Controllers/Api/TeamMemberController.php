<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamMemberController extends Controller
{
    /**
     * Mostra todas as associações de membros de um time
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

        $page     = (int) $request->get('page');
        $length   = (int) $request->get('length');
        $orderBy  = $request->get('order_by');
        $orderDir = $request->get('order_dir');

        $team_members = TeamMember::orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $response = [
            'data'     => $team_members,
            'total'    => TeamMember::count(),
        ];
        return response()->json($response);
    }

    /**
     * Cria uma associação de um membro a um time
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'member_id'         => 'required|exists:users,id',
            'team_id'           => 'required|exists:teams,id',
            'is_team_leader'    => 'bool',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $count_team_members = TeamMember::where('member_id', $request->get('member_id'))
        ->where('team_id', $request->get('team_id'))
        ->count();

        if($count_team_members > 0){
            return response()->json(['message' => 'O membro em questão já faz parte do time em questão.'], 400);
        }

        $data = $request->all();
        $team_member = TeamMember::create($data);

        if ($team_member) {
                return response()->json(['message' => 'Associação criada com sucesso']);
        } else {
            return response()->json(['message' => 'Erro ao criar associação'], 400);
        }
    }

    /**
     * Mostra uma associação incluindo informaçõe de membro e time a partir do id da associação
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:team_members',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $team_member = TeamMember::with('member')
        ->with('team')
        ->find($id);

        return response()->json($team_member);
    }

    /**
     * Atualiza uma associação de um membro com um time a partir do id da associação
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'                => 'required|int|exists:team_members',
            'member_id'         => 'exists:users,id',
            'team_id'           => 'exists:teams,id',
            'is_team_leader'    => 'bool',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $team_member = TeamMember::find($id);
        $team_member->member_id = $request->get('member_id', $team_member->member_id);
        $team_member->team_id = $request->get('team_id', $team_member->team_id);
        $team_member->is_team_leader = $request->get('is_team_leader', $team_member->is_team_leader);
        $team_member->save();

        return response()->json($team_member);
    }

    /**
     * Apaga uma associação de um membro com um time a partir do id da associação
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'    => 'required|int|exists:team_members',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $team_member = TeamMember::find($id);
        $team_member->delete();

        return response()->json(['message' => 'Associação deletada com sucesso']);
    }
}
