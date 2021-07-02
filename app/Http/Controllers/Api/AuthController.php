<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendMailRecoveryPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'recovery', 'testRecoveryCode', 'updatePassword']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Gera um token e envia para o email do usuário para que ele consiga alterar a senha posteriormente.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recovery(Request $request)
    {
        $user = User::where("email", $request->get("email"))->first();

        if ($user == null) {
            return response()->json(['error' => 'User not found'], 400);
        }

        $code = rand(100000, 999999);

        $user->recovery_code = bcrypt($code);
        $user->recovery_date = date('Y-m-d H:i:s');

        $user->save();

        if (Mail::to($user->email)->send(new SendMailRecoveryPassword($user->email, $code)) === null) {
            return response()->json(["message" => "E-mail enviado com sucesso"]);
        } else {
            return response()->json(["message" => "Falha ao enviar e-mail"], 500);
        }
    }

    /**
     * Valida se um token é valido ou não.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testRecoveryCode(Request $request)
    {
        $timeValid = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')) - 3600 * 6);
        $users     = DB::table("users")->where("recovery_date", ">", $timeValid)->get();

        $valid  = false;
        $userId = null;
        foreach($users as $user) {
            if (Hash::check($request->get('code'), $user->recovery_code)) {
                $valid  = true;
                $userId = $user->id;
                break;
            }
        }

        return ["valid" => $valid, "user_id" => $userId];
    }

    /**
     * Atualiza uma senha baseado no token do usuário e id.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "user_id"  => "required|exists:users,id",
            "code"     => "required|integer",
            "password" => "required|min:6|max:10|confirmed"
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $user = User::find($request->get("user_id"));

        if (!Hash::check($request->get('code'), $user->recovery_code)) {
            return response()->json(["message" => "O código de recuperação de senha não pertence a este usuário."], 400);
        }

        $user->password      = bcrypt($request->get("password"));
        $user->recovery_code = null;
        $user->recovery_date = null;

        if ($user->save()) {
            return response()->json(["message" => "Senha alterada com sucesso"]);
        } else {
            return response()->json(["message" => "Falha ao alterar senha"], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 * 24
        ]);
    }

}
