<?php

namespace App\Repositories;

use App\DataObject\Address as AddressDataObject;
use App\Models\Address;

class AddressRepository
{

    private $model;
    private $cityRepository;

    function __construct(
        Address $model,
        CityRepository $cityRepository
    ) {
        $this->model = $model;
        $this->cityRepository = $cityRepository;
    }

    public function add(AddressDataObject $addressDataObject): Address
    {
        $city = $this->cityRepository->get($addressDataObject);

        return $this->model->create([
            'street'        => $addressDataObject->getStreet(),
            'number'        => $addressDataObject->getNumber(),
            'complement'    => $addressDataObject->getComplement(),
            'neighborhood'  => $addressDataObject->getNeighborhood(),
            'zipcode'       => $addressDataObject->getZipcode(),
            'city_id'       => $city->id
        ]);
    }

    public function update(AddressDataObject $addressDataObject, int $id): Address
    {
        $city = $this->cityRepository->get($addressDataObject);

        $this->model->where('id', $id)
            ->update([
                'street'        => $addressDataObject->getStreet(),
                'number'        => $addressDataObject->getNumber(),
                'complement'    => $addressDataObject->getComplement(),
                'neighborhood'  => $addressDataObject->getNeighborhood(),
                'zipcode'       => $addressDataObject->getZipcode(),
                'city_id'       => $city->id
            ]);

        return $this->getFull($id);
    }

    public function getFull(int $id)
    {
        return $this->model
            ->select(
                'addresses.id as address_id',
                'cities.id as city_id',
                'states.id as state_id',
                'countries.id as country_id',
                'addresses.street',
                'addresses.number',
                'addresses.complement',
                'addresses.neighborhood',
                'addresses.zipcode',
                'cities.name as city',
                'states.uf as uf',
                'countries.name as country',
            )
            ->join('cities', 'cities.id', '=', 'addresses.city_id')
            ->join('states', 'states.id', '=', 'cities.state_id')
            ->join('countries', 'countries.id', '=', 'states.country_id')
            ->where('addresses.id', $id)
            ->first();
    }
}
