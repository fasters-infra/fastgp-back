<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyGoal;
use Illuminate\Http\Request;
use Validator;

class CompanyGoalController extends Controller
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
            'order_by'   => 'required|in:id,date,value',
            'order_dir'  => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $page      = (int) $request->get("page");
        $length    = (int) $request->get("length");
        $orderBy   = $request->get("order_by");
        $orderDir  = $request->get("order_dir");
        $dateStart = $request->get("date_start");
        $dateEnd   = $request->get("date_end");

        $companyGoals =
            CompanyGoal::when($dateStart, function ($query, $dateStart) {
                return $query->where("date", ">=", $dateStart);
            })
            ->when($dateEnd, function ($query, $dateEnd) {
                return $query->where("date", "<=", $dateEnd);
            })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)
            ->select([
                "id",
                "date",
                "goal"
            ])
            ->get();

        $recordsFiltered =
            CompanyGoal::when($dateStart, function ($query, $dateStart) {
                return $query->where("date", ">=", $dateStart);
            })
            ->when($dateEnd, function ($query, $dateEnd) {
                return $query->where("date", "<=", $dateEnd);
            })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)
            ->select([
                "id",
                "date",
                "goal"
            ])
            ->count();

        $response = [
            "data"     => $companyGoals,
            "total"    => CompanyGoal::count(),
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
            'date' => 'required|date_format:Y-m-d',
            'goal' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $companyGoal = new CompanyGoal();
        $companyGoal->date = $request->get('date');
        $companyGoal->goal = $request->get('goal');

        if ($companyGoal->save()) {
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
        $companyGoal = CompanyGoal::find($id);

        if ($companyGoal == null) {
            return response()->json(["message" => "Registro não encontrado no banco de dados", 400]);
        }

        return response()->json($companyGoal);
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
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'   => 'required|int|exists:company_goals',
            'date' => 'required|date_format:Y-m-d',
            'goal' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $companyGoal = CompanyGoal::find($id);
        $companyGoal->date = $request->get('date');
        $companyGoal->goal = $request->get('goal');

        if ($companyGoal->save()) {
            return response()->json($companyGoal);
        } else {
            return response()->json(["message" => "Falha ao salvar dados da meta da empresa no banco de dados", 500]);
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
        $companyGoal = CompanyGoal::find($id);

        if ($companyGoal == null) {
            return response()->json(["message" => "Registro não encontrado no banco de dados", 400]);
        }

        if ($companyGoal->delete()) {
            return response()->json(['message' => 'Registro removido com sucesso']);
        } else {
            return response()->json(["message" => "Falha ao remover dados da meta da empresa no banco de dados", 500]);
        }
    }
}
