<?php

namespace App\Http\Controllers\Api;

use App\DataObject\Address;
use App\Http\Controllers\Controller;
use App\Models\Address as AddressModel;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\State;
use App\Repositories\AddressRepository;
use App\Repositories\CityRepository;
use App\Repositories\CountryRepository;
use App\Repositories\StateRepository;
use Illuminate\Http\Request;
use Validator;

class CompanyController extends Controller
{
    /**
     * Função responsável por realizar a listagem de empresas.
     *
     * @bodyParam page int required A página solicitada (A contagem de páginas é iniciada no número zero). Exemplo: 4
     * @bodyParam length int required A quantidade de registros a serem retornados. Exemplo: 50
     * @bodyParam search string String com busca realizada pelo usuário no sistema. Exemplo: Bruno
     * @bodyParam order_by string required Define qual coluna será usada como ordenação. Exemplo: name
     * @bodyParam order_dir string required Define a ordenação da listagem: asc/desc. Exemplo: desc
     *
     * @return object
     */
    public function index(Request $request)
    {
        $validation = Validator::make($request->all(),[
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

        $companies = Company::where(function($query) use ($search) {
                $search = "%" . $search . "%";
                return $query->where('name', 'like', $search)
                    ->orWhere('fantasy_name', 'like', $search);
            })
            ->orderBy($orderBy, $orderDir)
            ->skip($page * $length)
            ->take($length)->get();

        $recordsFiltered = Company::where(function($query) use ($search) {
                $search = "%" . $search . "%";
                return $query->where('name', 'like', $search)
                    ->orWhere('fantasy_name', 'like', $search);
            })->count();

        $response = [
            "data"     => $companies,
            "total"    => Company::count(),
            "filtered" => $recordsFiltered
        ];

        return response()->json($response);
    }

    /**
     * Cria uma empresa do banco de dados. Método não implementado ainda.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response("Not implemented yet", 501);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::find($id);

        if(!empty($company->address_id)){
            $addressRepository = new AddressRepository(
                new AddressModel(),
                new CityRepository(new City(), new StateRepository(new State(), new CountryRepository(new Country())))
            );
            $company->address = $addressRepository->getFull($company->address_id);
        }

        if ($company == null) {
            return response()->json(["message" => "Empresa não encontrada"], 400);
        } else {
            return response()->json($company);
        }
    }

    /**
     * Atualiza uma empresa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @bodyParam name string required Razão social da empresa. Exemplo: Fasters Razão Social
     * @bodyParam fantasy_name string required Nome Fantaria da empresa. Exemplo: Fasters Nome Fantasia
     * @bodyParam foundation date Data que a empresa foi fundada. Exemplo: 1970-01-01
     * @bodyParam website string Website da empresa. Exemplo: https://www.fasters.com.br/
     * @bodyParam phone string Telefone da empresa formatado. Exemplo: (11) 1111-1111 ou (11) 11111-1111
     * @bodyParam street string Rua/Logradouro que a empresa está localizada. Exemplo: Rua de exemplo
     * @bodyParam number string Número da empresa referente ao endereço. Exemplo: 62
     * @bodyParam complement string Complemento do endereço. Exemplo: Sala 404
     * @bodyParam neighborhood string Bairro. Exemplo: Bairro de exemplo
     * @bodyParam zipcode string CEP. Exemplo: 11111-111
     * @bodyParam city_id int ID da cidade da empresa baseada na tabela cities. Exemplo: 6
     * @bodyParam description string Descrição da empresa. Exemplo: Empresa filial da cidade de exemplo.
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]),[
            'id'                => 'required|int|exists:companies',
            'name'              => 'required|min:5|max:255',
            'fantasy_name'      => 'required|min:5|max:255',
            'foundation'        => 'date',
            'website'           => 'url|max:255',
            'phone'             => 'min:14|max:15',
            'description'       => 'max:255',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        $company = Company::find($id);

        $company->name         = $request->get('name');
        $company->fantasy_name = $request->get('fantasy_name');
        $company->foundation   = $request->get('foundation');
        $company->website      = $request->get('website');
        $company->phone        = $request->get('phone');
        $company->description  = $request->get('description');

        if ($company->save()) {
            return response()->json($company);
        } else {
            return response()->json(["message" => "Falha ao salvar dados da empresa no banco de dados", 500]);
        }
    }

    /**
     * Remove uma empresa do banco de dados. Método não implementado ainda.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response("Not implemented yet", 501);
    }
}
