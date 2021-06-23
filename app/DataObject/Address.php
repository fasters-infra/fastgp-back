<?php

namespace App\DataObject;

class Address
{
    private $street;
    private $number;
    private $complement;
    private $neighborhood;
    private $zipcode;
    private $city;
    private $uf;
    private $county;
    private $countyInitials;

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getComplement(): string
    {
        return $this->complement;
    }

    public function getNeighborhood(): string
    {
        return $this->neighborhood;
    }

    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getUf(): string
    {
        return $this->uf;
    }

    public function getCounty(): string
    {
        return $this->county;
    }

    public function getCountyInitials(): string
    {
        return $this->countyInitials;
    }

    public function setStreet(string $street): Address
    {
        $this->street = $street;
        return $this;
    }

    public function setNumber(string $number): Address
    {
        $this->number = $number;
        return $this;
    }

    public function setComplement(string $complement): Address
    {
        $this->complement = $complement;
        return $this;
    }

    public function setNeighborhood(string $neighborhood): Address
    {
        $this->neighborhood = $neighborhood;
        return $this;
    }

    public function setZipcode(string $zipcode): Address
    {
        $this->zipcode = $zipcode;
        return $this;
    }

    public function setCity(string $city): Address
    {
        $this->city = $city;
        return $this;
    }

    public function setUf(string $uf): Address
    {
        $this->uf = $uf;
        return $this;
    }

    public function setCounty(string $county): Address
    {
        $this->county = $county;
        return $this;
    }

    public function setCountyInitials(string $countyInitials): Address
    {
        $this->countyInitials = $countyInitials;
        return $this;
    }
}
