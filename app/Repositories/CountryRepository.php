<?php

namespace App\Repositories;

use App\DataObject\Address;
use App\Models\Country;

class CountryRepository
{
    private $model;

    function __construct(Country $model)
    {
        $this->model = $model;

    }

    public function get(Address $addressDataObject): Country
    {
        $country = $this->model->where('name', $addressDataObject->getCounty())->first();

        if(!empty($country)){
            return $country;
        }

        return $this->model->create([
            'name' => $addressDataObject->getCounty(),
            'initials' => $addressDataObject->getCountyInitials()
        ]);
    }
}
