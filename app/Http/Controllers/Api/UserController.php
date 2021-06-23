<?php

namespace App\Http\Controllers\Api;

use App\DataObject\Address;
use App\Http\Controllers\Controller;
use App\Models\Address as AddressModel;
use App\Models\City;
use App\Models\Country;
use App\Models\RequiredFields;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Role;
use App\Models\State;
use App\Repositories\AddressRepository;
use App\Repositories\CityRepository;
use App\Repositories\CountryRepository;
use App\Repositories\StateRepository;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role as UserRoles;
use Spatie\Permission\Models\Permission;
use App\Services\DBServices;
use Illuminate\Support\Facades\Validator;
use DB;

class UserController extends Controller
{
    /**
     * Função responsável por realizar a listagem de usuários.
     *
     * @bodyParam page int required A página solicitada (A contagem de páginas é iniciada no número zero). Exemplo: 4
     * @bodyParam length int required A quantidade de registros a serem retornados. Exemplo: 50
     * @bodyParam search string String com busca realizada pelo usuário no sistema. Exemplo: Bruno
     * @bodyParam order_by string required Define qual coluna será usada como ordenação. Exemplo: name
     * @bodyParam order_dir string required Define a ordenação da listagem: asc/desc. Exemplo: desc
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'page'      => 'required|int|min:0',
            'length'    => 'required|int|min:1|max:100',
            'order_by'  => 'required|in:id,name',
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

        $users = User::where("status", "active")
            ->where(function ($query) use ($search) {
                $search = "%{$search}%";
                return $query->where('name', 'like', $search)
                    ->orWhere('cpf', 'like', $search);
            })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $recordsFiltered = User::where("status", "active")
            ->where(function ($query) use ($search) {
                $search = "%{$search}%";
                return $query->where('name', 'like', $search)
                    ->orWhere('cpf', 'like', $search);
            })->count();

        $response = [
            "data"     => $users,
            "total"    => User::where("status", "active")->count(),
            "filtered" => $recordsFiltered
        ];

        return response()->json($response);
    }

    /**
     * Inclui um novo usuário
     *
     * @bodyParam name string required Nome do usuário
     * @bodyParam email string required Email do usuário
     * @bodyParam password string required Senha do do usuário
     * @bodyParam confirm_password string required Senha do do usuário
     * @bodyParam street string Endereço do usuário
     * @bodyParam number string Número do endereço do usuário
     * @bodyParam complement string Complemento do endereço do usuário
     * @bodyParam neighborhood string Bairro do usuário
     * @bodyParam zipcode string CEP do usuário
     * @bodyParam cpf string CPF do usuário
     * @bodyParam rg string RG do usuário
     * @bodyParam birthday date Data de nascimento do usuário
     * @bodyParam photo string URL da imagem de perfil do usuário
     * @bodyParam phone string Telefone do usuário
     * @bodyParam cellphone string required Celular do usuário
     * @bodyParam natural string Naturalidade do usuário
     * @bodyParam nationality string Nacionalidade do usuário
     * @bodyParam marital_status string Estado civil do usuário
     * @bodyParam status string Status do cadastro do usuário. Exemplo: active, inactive
     * @bodyParam role_id int required
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $dbService = new DBServices();
        $enumStatusValues = $dbService->getEnumValues('users', 'status');
        $enumStatusValues = $enumStatusValues ? "|in:{$enumStatusValues}" : "";

        $enumScholValues = $dbService->getEnumValues('users', 'scholarity');
        $enumScholValues = $enumScholValues ? "|in:{$enumScholValues}" : "";

        $enumGenderValues = $dbService->getEnumValues('users', 'gender');
        $enumGenderValues = $enumGenderValues ? "|in:{$enumGenderValues}" : "";

        $enumWorkloadPeriod = $dbService->getEnumValues('user_occupations', 'workload_period');
        $enumWorkloadPeriod = $enumWorkloadPeriod ? "|in:{$enumWorkloadPeriod}" : "";

        $requiredFields = RequiredFields::where('type', 1)->where('required', true)->get();
        $requiredFieldsArray = [];

        foreach ($requiredFields as $requiredField) {

            $requiredFieldsArray[$requiredField->name] = "required";
        }

        $validation = Validator::make($request->all(), [
            'name'                          => $requiredFieldsArray['name']  ?? 'nullable' . '|string|min:2',
            'email'                         => $requiredFieldsArray['email'] ?? 'nullable' . '|email:rfc|unique:users,email,NULL,id,deleted_at,NULL',
            'password'                      => $requiredFieldsArray['password'] ?? 'nullable' . '|string|min:6|max:10',
            'confirm_password'              => $requiredFieldsArray['confirm_password'] ?? 'nullable' . '|string|min:6|max:10',
            'cellphone'                     => $requiredFieldsArray['cellphone'] ?? 'nullable' . '|string|min:11',
            'role_id'                       => $requiredFieldsArray['role_id'] ?? 'nullable' . '|int',
            'cpf'                           => $requiredFieldsArray['cpf'] ?? 'nullable' . '|string|min:11',
            'cnpj'                          => $requiredFieldsArray['cnpj'] ?? 'nullable' . '|string|min:14',
            'rg'                            => $requiredFieldsArray['rg'] ?? 'nullable' . '|string',
            'birthday'                      => $requiredFieldsArray['birthday'] ?? 'nullable' . '|date|date_format:Y-m-d',
            'photo'                         => $requiredFieldsArray['photo'] ?? 'nullable' . '|file',
            'phone'                         => $requiredFieldsArray['phone'] ?? 'nullable' . '|string',
            'natural'                       => $requiredFieldsArray['natural'] ?? 'nullable' . '|string',
            'nationality'                   => $requiredFieldsArray['nationality'] ?? 'nullable' . '|string',
            'marital_status'                => $requiredFieldsArray['marital_status'] ?? 'nullable' . '|string',
            'status'                        => $requiredFieldsArray['status'] ?? 'nullable' . "|string{$enumStatusValues}",
            'scholarity'                    => $requiredFieldsArray['scholarity'] ?? 'nullable' . "|string{$enumScholValues}",
            'gender'                        => $requiredFieldsArray['gender'] ?? 'nullable' . "|string{$enumGenderValues}",
            'stacks'                        => $requiredFieldsArray['stacks'] ?? 'nullable' . '|exists:stacks,id',
            'team_id'                       => $requiredFieldsArray['team_id'] ?? 'nullable' . '|exists:teams,id',
            'ocupation_levels_id'           => $requiredFieldsArray['ocupation_levels_id'] ?? 'nullable' . '|exists:occupation_levels,id',
            'init_hour'                     => $requiredFieldsArray['init_hour'] ?? 'nullable' . '|date_format:H:i',
            'end_hour'                      => $requiredFieldsArray['end_hour'] ?? 'nullable' . '|date_format:H:i',
            'occupation_observation'        => $requiredFieldsArray['occupation_observation'] ?? 'nullable' . '|string',
            'occupation_workload'           => $requiredFieldsArray['occupation_workload'] ?? 'nullable' . '|int',
            'occupation_workload_period'    => $requiredFieldsArray['occupation_workload_period'] ?? 'nullable' . "|string{$enumWorkloadPeriod}",
            'occupation_hour_value'         => $requiredFieldsArray['occupation_hour_value'] ?? 'nullable' . '|numeric',
            'street'                        => $requiredFieldsArray['street'] ?? 'nullable' . '|string',
            'number'                        => $requiredFieldsArray['number'] ?? 'nullable' . '|string',
            'complement'                    => $requiredFieldsArray['complement'] ?? 'nullable' . '|string',
            'neighborhood'                  => $requiredFieldsArray['neighborhood'] ?? 'nullable' . '|string',
            'zipcode'                       => $requiredFieldsArray['zipcode'] ?? 'nullable' . '|string|min:8',
            'city'                          => $requiredFieldsArray['city'] ?? 'nullable' . '|string',
            'uf'                            => $requiredFieldsArray['uf'] ?? 'nullable' . '|string',
            'country'                       => $requiredFieldsArray['country'] ?? 'nullable' . '|string',
            'country_initials'              => $requiredFieldsArray['country_initials'] ?? 'nullable' . '|string',
            'eletronic_point_profile_id'    => $requiredFieldsArray['eletronic_point_profile_id'] ?? 'nullable' . '|exists:eletronic_point_profiles,id',
            'social_reason'                 => $requiredFieldsArray['social_reason'] ?? 'nullable',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        if ($request->get('password') != $request->get('confirm_password')) {
            return response()->json(['password' => 'A senha e a confirmação não conferem'], 400);
        }

        try {
            $user                               = new User();
            $user->name                         = $request->get('name');
            $user->email                        = $request->get('email');
            $user->password                     = password_hash($request->get('password'), PASSWORD_BCRYPT);
            $user->cpf                          = $request->get('cpf');
            $user->rg                           = $request->get('rg');
            $user->birthday                     = $request->get('birthday');
            $user->phone                        = $request->get('phone');
            $user->cellphone                    = $request->get('cellphone');
            $user->natural                      = $request->get('natural');
            $user->nationality                  = $request->get('nationality');
            $user->marital_status               = $request->get('marital_status');
            $user->status                       = $request->get('status') || 'active';
            $user->scholarity                   = $request->get('scholarity');
            $user->role_id                      = $request->get('role_id');
            $user->gender                       = $request->get('gender');
            $user->init_hour                    = $request->get('init_hour');
            $user->end_hour                     = $request->get('end_hour');
            $user->cnpj                         = $request->get('cnpj');
            $user->ie                           = $request->get('ie');
            $user->eletronic_point_profile_id   = $request->get('eletronic_point_profile_id');
            $user->social_reason                = $request->get('social_reason');

            $user->assignRole($request->role_id);

            $idBucketService = new DBServices();
            $user->s3_bucket = $idBucketService->getUserS3BucketCode();

            if ($request->file("photo")) {
                $s3Storage = Storage::disk('s3');
                $storagePath = $s3Storage->put("users/{$user->s3_bucket}/profile", $request->file("photo"), 'public');
                $user->photo = $s3Storage->url($storagePath);
            }

            $addresDataObject = new Address();
            $addresDataObject->setStreet((string) $request->get('street'))
                ->setNumber((string) $request->get('number'))
                ->setComplement((string) $request->get('complement'))
                ->setNeighborhood((string) $request->get('neighborhood'))
                ->setZipcode((string) $request->get('zipcode'))
                ->setCity((string) $request->get('city'))
                ->setUf((string) $request->get('uf'))
                ->setCounty((string) $request->get('country'))
                ->setCountyInitials((string) $request->get('country_initials'));

            $addressRepository = new AddressRepository(
                new AddressModel(),
                new CityRepository(new City(), new StateRepository(new State(), new CountryRepository(new Country())))
            );

            $address = $addressRepository->add($addresDataObject);
            $user->address_id = $address->id;

            $user->save();

            $stacks = $request->get('stacks');
            if ($stacks) {
                $user->stacks()->attach($stacks);
            }

            $team_id = $request->get('team_id');
            if ($team_id) {
                $user->teams()->attach($team_id, [
                    "is_team_leader" => false
                ]);
            }

            $ocupation_levels_id = $request->get('ocupation_levels_id');
            if ($ocupation_levels_id) {
                $user->occupations()->attach($ocupation_levels_id, [
                    'start'             => date('Y-m-d'),
                    'observations'      => $request->get('occupation_observation'),
                    'workload'          => $request->get('occupation_workload'),
                    'workload_period'   => $request->get('occupation_workload_period'),
                    'hour_value'        => $request->get('occupation_hour_value'),
                ]);
            }

            return response()->json(['id' => $user->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao inserir o novo usuário no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Recupera um usuário pelo seu id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id', (int)$id)->with(['userOccupations', 'socialMedias', 'teams', 'stacks'])->first();
        $user->address = [];
        if (!empty($user->address_id)) {
            $addressRepository = new AddressRepository(
                new AddressModel(),
                new CityRepository(new City(), new StateRepository(new State(), new CountryRepository(new Country())))
            );
            $user->address = $addressRepository->getFull($user->address_id);
        }

        if (empty($user)) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }
        return response()->json(['data' => $user], 200);
    }


    /**
     * Atualiza os dados do usuário.
     *
     * @param  int  $id
     *
     * @bodyParam name string required Nome do usuário
     * @bodyParam street string Endereço do usuário
     * @bodyParam number string Número do endereço do usuário
     * @bodyParam complement string Complemento do endereço do usuário
     * @bodyParam neighborhood string Bairro do usuário
     * @bodyParam zipcode string CEP do usuário
     * @bodyParam city_id int ID da cidade do usuário
     * @bodyParam cpf string CPF do usuário
     * @bodyParam rg string RG do usuário
     * @bodyParam birthday date Data de nascimento do usuário
     * @bodyParam photo string URL da imagem de perfil do usuário
     * @bodyParam phone string Telefone do usuário
     * @bodyParam cellphone string required Celular do usuário
     * @bodyParam natural string Naturalidade do usuário
     * @bodyParam nationality string Nacionalidade do usuário
     * @bodyParam marital_status string Estado civil do usuário
     * @bodyParam status string Status do cadastro do usuário. Exemplo: active, inactive
     * @bodyParam role_id int required
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $dbService = new DBServices();
        $enumStatusValues = $dbService->getEnumValues('users', 'status');
        $enumStatusValues = $enumStatusValues ? "|in:{$enumStatusValues}" : "";

        $enumScholValues = $dbService->getEnumValues('users', 'scholarity');
        $enumScholValues = $enumScholValues ? "|in:{$enumScholValues}" : "";

        $enumGenderValues = $dbService->getEnumValues('users', 'gender');
        $enumGenderValues = $enumGenderValues ? "|in:{$enumGenderValues}" : "";

        $requiredFields = RequiredFields::where('type', 1)->where('required', true)->get();
        $requiredFieldsArray = [];

        foreach ($requiredFields as $requiredField) {

            $requiredFieldsArray[$requiredField->name] = "required";
        }

        $validation = Validator::make($request->all(), [
            'name'                      => 'required|string|min:2',
            'cellphone'                 => 'required|string|min:11',
            'role_id'                   => "required|int",
            'cpf'                       => 'nullable|string|min:11',
            'rg'                        => 'nullable|string',
            'birthday'                  => 'nullable|date|date_format:Y-m-d',
            'photo'                     => 'nullable|file',
            'phone'                     => 'nullable|string',
            'natural'                   => 'nullable|string',
            'nationality'               => 'nullable|string',
            'marital_status'            => 'nullable|string',
            'status'                    => "nullable|string{$enumStatusValues}",
            'scholarity'                => $requiredFieldsArray['scholarity'] ?? 'nullable' . "|string{$enumScholValues}",
            'gender'                    => $requiredFieldsArray['gender'] ?? 'nullable' . "|string{$enumGenderValues}",
            'stacks'                    => $requiredFieldsArray['stacks'] ?? 'nullable' . '|exists:stacks,id',
            'eletronic_point_profile_id'   => 'nullable|exists:eletronic_point_profiles,id',
        ]);


        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $user = User::find((int)$id);
        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $user->name                     = $request->get('name');
        $user->cpf                      = $request->get('cpf');
        $user->rg                       = $request->get('rg');
        $user->birthday                 = $request->get('birthday');
        $user->photo                    = $request->get('photo');
        $user->phone                    = $request->get('phone');
        $user->cellphone                = $request->get('cellphone');
        $user->natural                  = $request->get('natural');
        $user->nationality              = $request->get('nationality');
        $user->marital_status           = $request->get('marital_status');
        $user->status                   = $request->get('status');
        $user->scholarity               = $request->get('scholarity');
        $user->gender                   = $request->get('gender');
        $user->eletronic_point_profile_id  = $request->get('eletronic_point_profile_id') ?? $user->eletronic_point_profile_id;


        if ($request->file("photo")) {
            $s3Storage = Storage::disk('s3');
            $storagePath = $s3Storage->put("users/{$user->s3_bucket}/profile", $request->file("photo"), 'public');
            $user->photo = $s3Storage->url($storagePath);
        }

        try {
            $user->save();

            $stacks = $request->get('stacks');
            if ($stacks) {
                $user->stacks()->detach();
                $user->stacks()->attach($stacks);
            }

            return response()->json(['id' => $user->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o usuário no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Atualiza o status de um usuário da base de dados.
     *
     * @param  int  $id
     * @bodyParam status string Status do usuário
     *
     */
    public function updateStatus(Request $request, $id)
    {
        $enumValues = new DBServices();
        $enumValues = $enumValues->getEnumValues('users', 'status');
        $enumValues = $enumValues ? "|in:{$enumValues}" : "";

        $validation = Validator::make($request->all(), [
            'status' => "required|string{$enumValues}"
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $user = User::whereNull('deleted_at')->where('id', $id)->first();

        if (empty($user)) {
            return response()->json(['message' => 'Usuário não encontrado'], 400);
        }

        $user->status = $request->get('status');

        try {
            $user->save();
            return response()->json(['id' => $user->id], 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao atualizar o status do usuário no banco de dados'
            ];
            return response()->json($response, 500);
        }
    }

    /**
     * Remove um usuário da base de dados.
     *
     * @param int  $id ID do usuário
     */
    public function destroy($id)
    {
        $authUser = auth('api')->user();

        $user = User::where('id', $id)->where('id', '<>', $authUser->id)->first();

        if(empty($user)){
            return response()->json(['message' => 'Usuário não encontrado'], 400);
        }

        $user->delete();

        return response()->json(['id' => $user->id], 200);
    }

    public function birthdays($month)
    {
        $month = (int)$month;
        if ($month < 1 || $month > 12) {
            return response()->json(['month' => 'O valor do mês tem que ser maior que 0 e menor que 13.'], 400);
        }

        try {
            $month = str_pad($month, 0, '0', STR_PAD_LEFT);
            $birthdays = DB::table('users')
                ->select(
                    'name',
                    'email',
                    'cpf',
                    'rg',
                    'birthday',
                    'photo',
                    'phone',
                    'cellphone',
                    'natural',
                    'nationality',
                    'marital_status',
                    'role_id',
                    'created_at',
                    'updated_at'
                )
                ->whereMonth("birthday", $month)
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->orderByRaw('DAY(birthday) ASC')
                ->get();
            return response()->json($birthdays, 200);
        } catch (\Throwable $th) {
            $response = [
                'dev_msg' => $th->getMessage(),
                'message' => 'Erro ao buscar os aniversariantes do banco de dados'
            ];
            return response()->json($response, 500);
        }
    }
}
