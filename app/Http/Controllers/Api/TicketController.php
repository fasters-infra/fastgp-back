<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class TicketController extends Controller
{

    public function index()
    {
        $ticket = Ticket::get();
        return response()->json(["ticket" => $ticket]);
    }

    public function view()
    {
        $user = auth()->user();
        $ticket = DB::table('ticket')->where('user_id', '=', $user->id)->get();
        return $ticket;
    }

    public function show(Ticket $ticket)
    {
        $teste = DB::table('ticket')->get();
        return response()->json(["teste" => $teste, 'user' => auth()->user()]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'name'        => 'required|min:5|max:255',
            'topic'       => 'required|min:5|max:255',
            'departament' => 'required|min:2|max:255',
            'message'     => 'required|min:5|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        // $userToken = Auth::user()->s3_bucket;

        $ticket = Ticket::create([

            "user_id"     => $request->input("user_id"),
            "name"        => $request->input("name"),
            "topic"       => $request->input("topic"),
            "departament" => $request->input("departament"),
            "message"     => $request->input("message"),
            //$storagePath = Storage::disk('s3')->put("user/{ $userToken }/tickets", $request->file("archive"), 'public'),
            //"archive" => Storage::disk('s3')->url($storagePath),

        ]);

        return $ticket;
    }



    public function update(Request $request, Ticket $ticket)
    {

        $validator = Validator::make($request->all(), [

            'name'        => 'required|min:5|max:255',
            'topic'       => 'required|min:5|max:255',
            'departament' => 'required|min:2|max:255',
            'message'     => 'required|min:5|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $ticket->name = $request->input('id');
        $ticket->name = $request->input('name');
        $ticket->topic = $request->input('topic');
        $ticket->departament = $request->input('departament');
        $ticket->message = $request->input('message');

        $ticket->save();

        return $ticket;
    }

    public function destroy(Ticket $ticket)
    {

        $ticket->delete();
        return response()->json(['sucess' => true]);
    }
}
