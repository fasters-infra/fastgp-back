<?php

namespace App\Repositories;

use App\DataObject\Address;
use App\Models\City;

class CityRepository
{
    private $model;
    private $stateRepository;

    function __construct(City $model, StateRepository $stateRepository)
    {
        $this->model = $model;
        $this->stateRepository = $stateRepository;
    }

    public function get(Address $addressDataObject): City
    {
        $city = $this->model->where('name', $addressDataObject->getCity())->first();

        if(!empty($city)){
            return $city;
        }

        $state = $this->stateRepository->get($addressDataObject);

        return $this->model->create([
            'name'      => $addressDataObject->getCity(),
            'state_id'  => $state->id
        ]);;
    }
}
