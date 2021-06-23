<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Files;
use Facade\FlareClient\Stacktrace\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UploadFilesController extends Controller
{
    public function index()
    {
        $files = Files::get();
        return response()->json(["files" => $files]);
    }

    public function show($id)
    {
        $file = Files::where('user_id', '=', $id)->get();
        return response()->json(["files" => $file]);
    }

    public function upload(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [

            'file'     => 'required',
            'sector'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $protocol = date("Ymd") . mt_rand() . $request->protocol;
        $userToken = Auth::user()->s3_bucket;

        $files = Files::create([

            'user_id' => $user->id,
            'sector'   => $request->input('sector'),
            'protocol' => $protocol,
            $file = Storage::disk('s3')->put("user/{ $userToken }/documents", $request->file("file"), 'public'),
            'file' => Storage::disk('s3')->url($file),

        ]);

        return response()->json($files, 200);
    }

    public function download($id)
    {
        $file = Files::find((int)$id);

        try {
            return response()->json(['file' => $file->file], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao fazer download do arquivo'
            ];
            return response()->json($response, 500);
        }
    }

    public function destroy($id)
    {
        $file = Files::find((int)$id);

        try {
            $file->delete();
            return response()->json(['id' => $file->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o documento'
            ];
            return response()->json($response, 500);
        }
    }
}
