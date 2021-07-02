<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\TeamMember;
use App\Services\DBServices;
use App\Services\NotificationServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Validator;

class NotificationController extends Controller
{
    /**
     * Função responsável por realizar a listagem de notificações.
     *
     * @bodyParam page int required A página solicitada (A contagem de páginas é iniciada no número zero). Exemplo: 2
     * @bodyParam length int required A quantidade de registros a serem retornados. Exemplo: 50
     * @bodyParam search string String com busca realizada pelo usuário no sistema. Exemplo: 'Promoção'
     * @bodyParam order_by string required Define qual coluna será usada como ordenação. Exemplo: title
     * @bodyParam order_dir string required Define a ordenação da listagem: asc/desc. Exemplo: desc
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dbService = new DBServices();
        $enumValues = $dbService->getEnumValues('notifications', 'status');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'status'    => "nullable|string{$enumValues}",
            'order_by'  => 'required|in:created_at,title,message',
            'order_dir' => 'required|in:asc,desc'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $page     = (int) $request->get("page");
        $length   = (int) $request->get("length");
        $search   = $request->get("search");
        $status   = $request->get('status');
        $orderBy  = $request->get("order_by");
        $orderDir = $request->get("order_dir");

        $notifications = Notification::where(function ($query) use ($search) {
            $query->where('title', 'like', "%$search%")
                ->orWhere('message', 'like', "%$search%");
        })->whereRaw("status = '$status' OR '$status' = ''")
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)
            ->get();

        $total = Notification::where(function ($query) use ($search) {
            $query->where('title', 'like', "%$search%")
                ->orWhere('message', 'like', "%$search%");
        })->whereRaw("status = '$status' OR '$status' = ''")
            ->count();

        $response = [
            "data"     => $notifications,
            "total"    => $total
        ];

        return response()->json($response);
    }

    /**
     * Inclui uma nova notificação
     *
     * @bodyParam title string required Título da notificação
     * @bodyParam type string required Tipo de notificação
     * @bodyParam message string required Conteúdo da notificação
     * @bodyParam user_id int required Usuário que cadastrou a notificação
     * @bodyParam payload string JSON de envio (usado pela API Node)
     * @bodyParam send_at datetime Envio programado
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dbService = new DBServices();
        $enumValues = $dbService->getEnumValues('notifications', 'type');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validationRules = [
            'title'     => 'required|string',
            'message'   => 'required|string',
            'type'      => "required|string{$enumValues}",
            'send_at'   => 'nullable|date|date_format:Y-m-d H:i:s',
        ];

        $payload = array('message' => $request->get('message'));

        if ($request->get('type') != 'broadcast') {
            array_merge($validationRules, [
                'recipients'    => 'required|array|min:1',
                'recipients.*'  => 'required|string|distinct|min:1',
                'payload'       => 'required|string',
            ]);

            $payload = array_merge($payload, [
                $request->get('type') => $request->get('recipients')
            ]);
        }

        $validation = Validator::make($request->all(), $validationRules);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        try {
            $user = Auth()->user();

            $notification          = new Notification();
            $notification->title   = $request->get('title');
            $notification->type    = $request->get('type');
            $notification->message = $request->get('message');
            $notification->user_id = $user->id;
            $notification->payload = json_encode($payload);
            $notification->send_at = $request->get('send_at');

            if (strtotime($notification->send_at) > time()) {
                $notification->status = 'scheduled';
            }

            $notification->save();

            $notifyService = new NotificationServices();
            $notifyService->sendNotification($notification->type, $payload);

            return response()->json(['id' => $notification->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir o registro no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Recupera uma notificação pelo seu id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notification = Notification::where('id', (int)$id)->get();
        if ($notification->isEmpty()) {
            return response()->json(['message' => 'Registro não encontrado'], 404);
        }
        return response()->json(['data' => $notification], 200);
    }


    /**
     * Atualiza uma notificação ainda não efetivada
     *
     * @param  int  $id ID da notificação
     *
     * @bodyParam title string required Título da notificação
     * @bodyParam type string required Tipo de notificação
     * @bodyParam message string required Conteúdo da notificação
     * @bodyParam user_id int required Usuário que cadastrou a notificação
     * @bodyParam payload string JSON de envio (usado pela API Node)
     * @bodyParam send_at datetime Envio programado
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $notification = Notification::where('id', '=', (int)$id)
            ->where('status', '!=', 'success')
            ->where('send_at', '>', DB::raw('NOW()'))
            ->get();

        if ($notification->isEmpty()) {
            return response()->json(['message' => 'Notificação não encontrada ou já efetivada'], 404);
        }

        $dbService = new DBServices();
        $enumValues = $dbService->getEnumValues('notifications', 'type');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'title'   => 'required|string',
            'message' => 'required|string',
            'user_id' => 'required|int',
            'type'    => "required|string{$enumValues}",
            'payload' => 'required|string',
            'send_at' => 'nullable|date|date_format:Y-m-d H:i:s',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $notification          = Notification::find((int)$id);
        $notification->title   = $request->get('title');
        $notification->type    = $request->get('type');
        $notification->message = $request->get('message');
        $notification->user_id = $request->get('user_id');
        $notification->payload = $request->get('payload');
        $notification->send_at = $request->get('send_at');

        if (strtotime($notification->send_at) > time()) {
            $notification->status = 'scheduled';
        }

        try {
            $notification->save();
            return response()->json(['id' => $notification->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o registro no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Remove uma notificação da base de dados.
     *
     * @param  int  $id ID da notificação
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = Notification::where('id', '=', (int)$id)
            ->where('status', '!=', 'success')
            ->where('send_at', '>', DB::raw('NOW()'))
            ->get();

        if ($notification->isEmpty()) {
            return response()->json(['message' => 'Notificação não encontrada ou já efetivada'], 404);
        }

        try {
            $notification = Notification::find((int)$id);
            $notification->delete();
            return response()->json(['id' => $notification->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao excluir o registro do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }
}
