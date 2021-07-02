<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\VacationSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;

class VacationScheduleController extends Controller
{
    /**
     * Mostra todos os agendamento de férias
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

        $vacation_schedules = VacationSchedule::orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $response = [
            "data"     => $vacation_schedules,
            "total"    => VacationSchedule::count(),
        ];
        return response()->json($response);
    }

    /**
     * Cria um novo agendamento de férias
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'initial_date'  => 'required|date',
            'end_date'      => 'required|date',
            'user_id'       => 'required|exists:users,id',
            'approver_id'   => 'required|exists:users,id',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $data = $request->all();
        $data['status'] = 'pendente';

        $user = User::find($request->get('user_id'));
        $initial_date = Carbon::createFromFormat('Y-m-d', $request->get('initial_date'));
        $end_date = Carbon::createFromFormat('Y-m-d', $request->get('end_date'));
        $user_initialize = $user->created_at;
        $user_schedule_date = $user_initialize->addDays(365);

        if ($initial_date < $user_schedule_date) {
            return response()->json(["message" => "O inicio de suas férias deve ser 365 dias a partir do seu inicio"], 400);
        }

        $initial_date->addDays(30);
        $max_days_date = Carbon::createFromFormat('Y-m-d', $initial_date->toDateString());
        $initial_date->addDays(-30);
        if (($end_date < $initial_date) || ($end_date > $max_days_date)) {
            return response()->json(["message" => "Você pode solicitar no máximo 30 dias de férias"], 400);
        }

        $vacation_schedule = VacationSchedule::create($data);

        if ($vacation_schedule) {
            return response()->json(["message" => "Agendamento de férias criado com sucesso", "vacation_schedule" => $vacation_schedule]);
        } else {
            return response()->json(["message" => "Erro ao o criar agendamento de férias"], 400);
        }
    }

    /**
     * Mostra apenas um agendamento de férias com as informações completas do usuário, usuário aprovado e status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:vacation_schedules',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $vacation_schedule = VacationSchedule::with('user')
            ->with('approver')
            ->find($id);

        return response()->json($vacation_schedule);
    }

    /**
     * Atualiza um agendamento de férias
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|int|exists:vacation_schedules',
            'initial_date'  => 'date',
            'end_date'      => 'date',
            'user_id'       => 'exists:users,id',
            'approver_id'   => 'exists:users,id',

        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $vacation_schedule = VacationSchedule::find($id);
        $vacation_schedule->initial_date = $request->get("initial_date", $vacation_schedule->initial_date);
        $vacation_schedule->end_date = $request->get("end_date", $vacation_schedule->end_date);
        $vacation_schedule->user_id = $request->get("user_id", $vacation_schedule->user_id);
        $vacation_schedule->approver_id = $request->get("approver_id", $vacation_schedule->approver_id);
        $vacation_schedule->save();

        return response()->json($vacation_schedule);
    }

    /**
     * Atualiza um agendamento de férias
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'            => 'required|int|exists:vacation_schedules',
            'status'        => 'required|in:approved,rejected',

        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $vacation_schedule = VacationSchedule::find($id);

        $user_logged = auth()->user();
        $approval_for_user = User::find($vacation_schedule->user_id);

        if (!$this->userHasPermission($user_logged, $approval_for_user)) {
            return response()->json(["message" => "Você não pode aprovar ou recusar uma solicitação de férias para esse usuário."], 400);
        }

        $vacation_schedule->status = $request->get("status", $vacation_schedule->status);
        $vacation_schedule->save();

        return response()->json($vacation_schedule);
    }

    /**
     * Apaga um agendamento de férias
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $validation = Validator::make(array_merge(['id' => $id]), [
            'id'            => 'required|int|exists:vacation_schedules',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $vacation_schedule = VacationSchedule::find($id);
        $vacation_schedule->delete();

        return response()->json(['message' => "Agendamento de férias deletado com sucesso"]);
    }

    private function userHasPermission($user, $approval_for)
    {

        if ($user->approver_for_all_teams) {
            return true;
        }


        $user_teams = TeamMember::select('id')
            ->where('member_id', $user->id)
            ->where('is_team_leader', true)
            ->get();

        foreach ($user_teams as $user_team) {
            $member_for_team_count = TeamMember::where('team_id', $user_team->id)
                ->where('member_id', $approval_for->id)
                ->count();

            if ($member_for_team_count > 0) {
                return true;
            }
        }

        return false;
    }
}
