<?php

namespace App\Http\Controllers\Api;

use App\DataObject\Address;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Repositories\AddressRepository;
use App\Repositories\CityRepository;
use App\Repositories\CountryRepository;
use App\Repositories\StateRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Address as AddressModel;
use App\Models\Company;
use App\Models\User;

class AddressController extends Controller
{
    public function update(Request $request, $id)
    {
        $validation = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id'                => 'required|exists:addresses,id',
            'street'            => 'required|string',
            'number'            => 'required|string',
            'complement'        => 'nullable|string',
            'neighborhood'      => 'required|string',
            'zipcode'           => 'required|string|min:8',
            'city'              => 'required|string',
            'uf'                => 'required|string',
            'country'           => 'required|string',
            'country_initials'  => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
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

        $address = $addressRepository->update($addresDataObject, $id);

        return response()->json($address);
    }


    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'user_id'           => 'exists:users,id',
            'company_id'        => 'exists:companies,id',
            'street'            => 'required|string',
            'number'            => 'required|string',
            'complement'        => 'nullable|string',
            'neighborhood'      => 'required|string',
            'zipcode'           => 'required|string|min:8',
            'city'              => 'required|string',
            'uf'                => 'required|string',
            'country'           => 'required|string',
            'country_initials'  => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages(), 400);
        }

        if (empty($request->get('user_id')) && empty($request->get('company_id'))) {
            return response()->json(['message' => 'Nenhum usuário ou empresa informado para esse endereço'], 400);
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

        if (!empty($request->get('user_id'))) {
            $user = User::where('id', $request->get('user_id'))->first();

            if (!empty($user->address_id)) {
                return response()->json(['messages' => 'Usuário já possui um endereço atrelado a ele.'], 400);
            }

            $user->address_id = $address->id;
            $user->save();

            return response()->json($address);
        }

        $company = Company::where('id', $request->get('company_id'))->first();

        if (!empty($company->address_id)) {
            return response()->json(['messages' => 'Empresa já possui um endereço atrelado a ele.'], 400);
        }

        $company->address_id = $address->id;
        $company->save();

        return response()->json($address);
    }
}
