<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stack;
use App\Models\UserStack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StackController extends Controller
{
    /**
     * Display a listing of the resource.
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
        $search   = $request->get("search");
        $orderBy  = $request->get("order_by");
        $orderDir = $request->get("order_dir");

        $medias = Stack::where('name', 'LIKE', "%{$search}%")
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $total = Stack::where('name', 'LIKE', "%{$search}%")
            ->count();

        $response = [
            "data"     => $medias,
            "total"    => $total
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
            'name'  => 'required|unique:stacks,name',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $stack = Stack::create([
            'name' => $request->get('name')
        ]);

        return response()->json(['stack' => $stack], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stack = Stack::with('users')->find($id);

        if (!$stack) {
            return response()->json(['message' => 'Stack não encontrada'], 400);
        }

        return response()->json(['data' => $stack], 200);
    }

    public function showStacks(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'stacks'    => 'required',
            'stacks.*'  => 'exists:stacks,id'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $stackIds = $request->get('stacks');
        $stack = UserStack::select('users.*')
        ->whereIn('user_stacks.stack_id', $stackIds)
        ->join('users', 'users.id', '=', 'user_stacks.user_id')
        ->whereNull('users.deleted_at')
        ->groupBy('user_stacks.user_id')
        ->get();

        if (!$stack) {
            return response()->json(['message' => 'Stack não encontrada'], 400);
        }

        return response()->json(['data' => $stack], 200);
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
        $validation = Validator::make($request->all(), [
            'name'  => 'required|unique:stacks,name,' . $id,
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $stack = Stack::find((int)$id);
        if (!$stack) {
            return response()->json(['message' => 'Stack não encontrada'], 400);
        }

        $stack->name = $request->get('name');
        $stack->save();

        return response()->json(['stack' => $stack], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stack = Stack::find((int)$id);

        if (!$stack) {
            return response()->json(['message' => 'Stack não encontrada'], 400);
        }

        $stack->delete();

        return response()->json(['message' => 'Stack apagada com sucesso'], 200);
    }
}
