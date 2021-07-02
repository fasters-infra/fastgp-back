<?php

namespace App\Services;

use App\Models\TeamMember;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * NotificationServices
 *
 * classe para serviços disparar serviços de notificação
 */
class NotificationServices
{
    private $apiUrl = 'http://192.168.0.106:3000/';
    private $jwt = null;
    private $return = array(
        'success' => false,
        'message' => '',
        'response' => null
    );

    public function __construct ()
    {
        $user = Auth()->user();
        $payload = [
            'email' => $user->email,
            'password' => $user->password,
        ];
        $endPoint = $this->apiUrl . 'auth';
        $response = Http::post($endPoint, $payload);

        if (empty($response->json())) {
            throw new Exception ('Autenticação da API falhou.');
        }

        $this->jwt = $response->json()[0]['token'];
    }

    /**
     * sendNotification
     *
     * retorna os valores possíveis para um enum
     *
     * @param  string $type
     * @param  string $payload
     * @return string
     */
    public function sendNotification($type, $payload)
    {
        if (!$this->jwt) {
            $this->return['message'] = 'Invalid credentials';
            return $this->return;
        }

        switch ($type) {
            case 'broadcast':
                $this->notifyAll($payload);
                break;
            case 'groups':
                $this->notifyGroups($payload);
                break;
            case 'clients':
                $this->notifyClients($payload);
                break;
            default:
                $this->return['message'] = 'Nothing to do: Bad payload';
                break;
        }

        if (array_key_exists('error', $this->return['response'])) {
            $this->return['message'] = 'CURL error';
        }

        return json_encode($this->return);
    }

    private function notifyAll($payload)
    {
        $endPoint = $this->apiUrl . 'groups';
        $this->return['response'] = Http::withHeaders([
            'x-access-token' => $this->jwt
        ])->post($endPoint, $payload);
    }

    private function notifyGroups($payload)
    {
        $recipients = array('clients' => array());
        foreach ($payload['groups'] as $value) {
            $members = TeamMember::where('team_id', $value)
                ->select('member_id')
                ->get();
            if ($members) {
                foreach ($members as $member) {
                    array_push($recipients['clients'], $member->member_id);
                }
            }
        }

        $payload = array_merge($payload, $recipients);
        unset($payload['groups']);

        $endPoint = $this->apiUrl . 'groups';
        $this->return['response'] = Http::withHeaders([
            'x-access-token' => $this->jwt
        ])->post($endPoint, $payload);
    }

    private function notifyClients($payload)
    {
        for ($i = 0; $i < count($payload['clients']); $i++) {
            $payload['clients'][$i] = md5($payload['clients'][$i]);
        }

        $endPoint = $this->apiUrl . 'clients';
        $this->return['response'] = Http::withHeaders([
            'x-access-token' => $this->jwt
        ])->post($endPoint, [
            $payload
        ]);
    }
}
