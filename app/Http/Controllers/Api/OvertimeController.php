<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use DB;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'       => 'required|int|min:0',
            'date_start' => 'date_format:Y-m-d',
            'date_end'   => 'date_format:Y-m-d',
            'length'     => 'required|int|min:1|max:100',
            'order_by'   => 'required|in:o.id,u.id,u.name,o.date_start,o.date_end,o.status,o.reason,o.changed_by,cb.name',
            'order_dir'  => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $page      = (int) $request->get("page");
        $length    = (int) $request->get("length");
        $search    = $request->get("search");
        $orderBy   = $request->get("order_by");
        $orderDir  = $request->get("order_dir");
        $dateStart = $request->get("date_start");
        $dateEnd   = $request->get("date_end");

        $overtimeRequests = DB::table("overtime_requests AS o")
            ->join("users AS u", "u.id", "=", "o.user_id")
            ->leftJoin("users AS cb", "cb.id", "=", "o.changed_by")
            ->where("u.id", Auth::user()->id)
            ->where(function ($query) use ($search) {
                $search = "%" . $search . "%";
                return $query->where('u.name', 'like', $search);
            })
            ->when($dateStart, function ($query, $dateStart) {
                return $query->where(function ($query) use ($dateStart) {
                    return $query->where("o.date_start", ">=", $dateStart)
                        ->orWhere(function ($query) use ($dateStart) {
                            return $query->where("o.date_start", "<=", $dateStart)
                                ->where("o.date_end", ">=", $dateStart);
                        });
                });
            })
            ->when($dateEnd, function ($query, $dateEnd) {
                return $query->where(function ($query) use ($dateEnd) {
                    return $query->where("o.date_end", "<=", $dateEnd)
                        ->orWhere(function ($query) use ($dateEnd) {
                            return $query->where("o.date_start", "<=", $dateEnd)
                                ->where("o.date_end", ">=", $dateEnd);
                        });
                });
            })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)
            ->select([
                "o.id",
                "u.id AS user_id",
                "u.name AS user_name",
                "o.date_start",
                "o.date_end",
                "o.status",
                "o.reason",
                "o.changed_by",
                "cb.name AS changed_by_name"
            ])->get();

        $recordsFiltered = DB::table("overtime_requests AS o")
            ->join("users AS u", "u.id", "=", "o.user_id")
            ->where("u.id", Auth::user()->id)
            ->where(function ($query) use ($search) {
                $search = "%" . $search . "%";
                return $query->where('u.name', 'like', $search);
            })
            ->when($dateStart, function ($query, $dateStart) {
                return $query->where(function ($query) use ($dateStart) {
                    return $query->where("o.date_start", ">=", $dateStart)
                        ->orWhere(function ($query) use ($dateStart) {
                            return $query->where("o.date_start", "<=", $dateStart)
                                ->where("o.date_end", ">=", $dateStart);
                        });
                });
            })
            ->when($dateEnd, function ($query, $dateEnd) {
                return $query->where(function ($query) use ($dateEnd) {
                    return $query->where("o.date_end", "<=", $dateEnd)
                        ->orWhere(function ($query) use ($dateEnd) {
                            return $query->where("o.date_start", "<=", $dateEnd)
                                ->where("o.date_end", ">=", $dateEnd);
                        });
                });
            })->count();

        $response = [
            "data"     => $overtimeRequests,
            "total"    => OvertimeRequest::where('user_id', Auth::user()->id)->count(),
            "filtered" => $recordsFiltered
        ];

        return response()->json($response);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexMyTeam(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'       => 'required|int|min:0',
            'date_start' => 'date_format:Y-m-d',
            'date_end'   => 'date_format:Y-m-d',
            'length'     => 'required|int|min:1|max:100',
            'order_by'   => 'required|in:o.id,u.id,u.name,o.date_start,o.date_end,o.status,o.reason,o.changed_by,cb.name',
            'order_dir'  => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $myTeams = TeamMember::where("member_id", Auth::user()->id)->where("is_team_leader", 1)->get()->pluck("team_id");

        $members = TeamMember::whereIn("team_id", $myTeams)->where("is_team_leader", 0)->get()->pluck("member_id");

        if (sizeOf($members) == 0) {
            return response()->json(["message" => "Você não possui membros em seu time"], 400);
        }

        $page      = (int) $request->get("page");
        $length    = (int) $request->get("length");
        $search    = $request->get("search");
        $orderBy   = $request->get("order_by");
        $orderDir  = $request->get("order_dir");
        $dateStart = $request->get("date_start");
        $dateEnd   = $request->get("date_end");

        $overtimeRequests = DB::table("overtime_requests AS o")
            ->join("users AS u", "u.id", "=", "o.user_id")
            ->leftJoin("users AS cb", "cb.id", "=", "o.changed_by")
            ->whereIn("u.id", $members)
            ->where(function ($query) use ($search) {
                $search = "%" . $search . "%";
                return $query->where('u.name', 'like', $search);
            })
            ->when($dateStart, function ($query, $dateStart) {
                return $query->where(function ($query) use ($dateStart) {
                    return $query->where("o.date_start", ">=", $dateStart)
                        ->orWhere(function ($query) use ($dateStart) {
                            return $query->where("o.date_start", "<=", $dateStart)
                                ->where("o.date_end", ">=", $dateStart);
                        });
                });
            })
            ->when($dateEnd, function ($query, $dateEnd) {
                return $query->where(function ($query) use ($dateEnd) {
                    return $query->where("o.date_end", "<=", $dateEnd)
                        ->orWhere(function ($query) use ($dateEnd) {
                            return $query->where("o.date_start", "<=", $dateEnd)
                                ->where("o.date_end", ">=", $dateEnd);
                        });
                });
            })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)
            ->select([
                "o.id",
                "u.id AS user_id",
                "u.name AS user_name",
                "o.date_start",
                "o.date_end",
                "o.status",
                "o.reason",
                "o.changed_by",
                "cb.name AS changed_by_name"
            ])->get();

        $recordsFiltered = DB::table("overtime_requests AS o")
            ->join("users AS u", "u.id", "=", "o.user_id")
            ->whereIn("u.id", $members)
            ->where(function ($query) use ($search) {
                $search = "%" . $search . "%";
                return $query->where('u.name', 'like', $search);
            })
            ->when($dateStart, function ($query, $dateStart) {
                return $query->where(function ($query) use ($dateStart) {
                    return $query->where("o.date_start", ">=", $dateStart)
                        ->orWhere(function ($query) use ($dateStart) {
                            return $query->where("o.date_start", "<=", $dateStart)
                                ->where("o.date_end", ">=", $dateStart);
                        });
                });
            })
            ->when($dateEnd, function ($query, $dateEnd) {
                return $query->where(function ($query) use ($dateEnd) {
                    return $query->where("o.date_end", "<=", $dateEnd)
                        ->orWhere(function ($query) use ($dateEnd) {
                            return $query->where("o.date_start", "<=", $dateEnd)
                                ->where("o.date_end", ">=", $dateEnd);
                        });
                });
            })->count();

        $response = [
            "data"     => $overtimeRequests,
            "total"    => OvertimeRequest::whereIn("user_id", $members)->count(),
            "filtered" => $recordsFiltered
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'reason'     => 'required',
            'date_start' => 'required|date_format:Y-m-d H:i',
            'date_end'   => 'required|date_format:Y-m-d H:i'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $overtime = new OvertimeRequest();
        $overtime->reason = $request->get('reason');
        $overtime->date_start = $request->get('date_start');
        $overtime->date_end = $request->get('date_end');
        $overtime->status = "pending";
        $overtime->user_id = Auth::user()->id;


        if ($overtime->save()) {
            return response()->json(["message" => "Registro salvo com sucesso"]);
        } else {
            return response()->json(["message" => "Falha ao salvar dados no banco de dados"], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return OvertimeRequest::find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ["id" => $id]), [
            'id'         => 'required|exists:overtime_requests,id',
            'reason'     => 'required',
            'date_start' => 'required|date_format:Y-m-d H:i',
            'date_end'   => 'required|date_format:Y-m-d H:i'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $overtime = OvertimeRequest::find($id);

        if ($overtime->user_id != Auth::user()->id) {
            return response()->json(["error" => "Essa solicitação pertence a outro usuário"], 400);
        }

        if ($overtime->status != "pending") {
            return response()->json(["error" => "A solicitação não está mais como pendente. Crie uma nova solicitação."], 400);
        }

        $overtime->reason = $request->get('reason');
        $overtime->date_start = $request->get('date_start');
        $overtime->date_end = $request->get('date_end');

        if ($overtime->save()) {
            return response()->json(["message" => "Registro salvo com sucesso"]);
        } else {
            return response()->json(["message" => "Falha ao salvar dados no banco de dados"], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $overtime = OvertimeRequest::find($id);

        if ($overtime == null) {
            return response()->json(["error" => "Solicitação não encontrada"], 400);
        }

        if ($overtime->user_id != Auth::user()->id) {
            return response()->json(["error" => "Essa solicitação pertence a outro usuário"], 400);
        }

        if ($overtime->status != "pending") {
            return response()->json(["error" => "A solicitação não está mais como pendente. Crie uma nova solicitação."], 400);
        }

        if ($overtime->delete()) {
            return response()->json(["message" => "Solicitação removida com sucesso"]);
        } else {
            return response()->json(["message" => "Falha ao remover solicitação do banco de dados"], 500);
        }
    }

    /**
     * Altera o status entre aprovado/reprovado da solicitação de horas extras.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($status, $id)
    {
        if (!in_array($status, ["denied", "approved"])) {
            return response()->json(["error" => "Status inválidos. Status aceitos: denied/approved"], 400);
        }

        $overtime = OvertimeRequest::find($id);

        if ($overtime == null) {
            return response()->json(["error" => "Solicitação não encontrada"], 400);
        }

        $teams = TeamMember::where("member_id", $overtime->user_id)->where("is_team_leader", 0)->get()->pluck("team_id");

        $leaders = TeamMember::whereIn("team_id", $teams)->where("is_team_leader", 1)->get()->pluck("member_id");

        if (!$leaders->contains(function($value) {return $value == Auth::user()->id;})) {
            return response()->json(["error" => "Você não possui permissões para fazer essa alteração"], 400);
        }

        $overtime->status = $status;
        $overtime->changed_by = Auth::user()->id;

        if ($overtime->save()) {
            return response()->json(["message" => "Registro salvo com sucesso"]);
        } else {
            return response()->json(["message" => "Falha ao salvar dados no banco de dados"], 500);
        }
    }
}
