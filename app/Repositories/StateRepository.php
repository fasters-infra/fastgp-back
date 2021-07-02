<?php

namespace App\Repositories;

use App\DataObject\Address;
use App\Models\State;

class StateRepository
{
    private $model;
    private $countryRepository;

    function __construct(State $model, CountryRepository $countryRepository)
    {
        $this->model = $model;
        $this->countryRepository = $countryRepository;
    }


    public function get(Address $addressDataObject): State
    {
        $state = $this->model->where('uf', $addressDataObject->getUf())->first();

        if(!empty($state)){
            return $state;
        }

        $county = $this->countryRepository->get($addressDataObject);

        return $this->model->create([
            'uf'            => $addressDataObject->getUf(),
            'country_id'    => $county->id
        ]);
    }
}
