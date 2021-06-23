<?php

namespace App\Repositories;

use App\DataObject\EletronicPointProfile as DataObjectEletronicPointProfile;
use App\DataObject\SeachIndex;
use App\Models\EletronicPointProfile;

class EletronicPointProfileRepository
{
    private $model;

    function __construct(EletronicPointProfile $model)
    {
        $this->model = $model;
    }

    public function filter(SeachIndex $search)
    {
        return $this->model->where(function ($query) use ($search) {
            $search = "%" . $search->getSearch() . "%";
            return $query->where('title', 'like', $search);
        })
            ->orderBy($search->getOrderBy(), $search->getOrderDir())
            ->skip($search->getPage() * $search->getLength())
            ->take($search->getLength())->get();
    }

    public function filtered(SeachIndex $search)
    {
        return $this->model->where(function ($query) use ($search) {
            $search = "%" . $search->getSearch() . "%";
            return $query->where('title', 'like', $search);
        })->count();
    }

    public function count()
    {
        return $this->model->count();
    }

    public function store(DataObjectEletronicPointProfile $eletronicPointProfile)
    {
        return $this->model->create([
            'title'                 => $eletronicPointProfile->getTitle(),
            'entry_time'            => $eletronicPointProfile->getEntryTime(),
            'break_time'            => $eletronicPointProfile->getBreakTime(),
            'interval_return_time'  => $eletronicPointProfile->getIntervalReturnTime(),
            'departure_time'        => $eletronicPointProfile->getDepartureTime(),
            'tolerance'             => $eletronicPointProfile->getTolerance()
        ]);
    }

    public function delete(int $id)
    {
        return $this->model->where('id', $id)->delete();
    }



    public function update(DataObjectEletronicPointProfile $eletronicPointProfile, int $id)
    {
        $this->model->where('id', $id)->update([
            'title'                 => $eletronicPointProfile->getTitle(),
            'entry_time'            => $eletronicPointProfile->getEntryTime(),
            'break_time'            => $eletronicPointProfile->getBreakTime(),
            'interval_return_time'  => $eletronicPointProfile->getIntervalReturnTime(),
            'departure_time'        => $eletronicPointProfile->getDepartureTime(),
            'tolerance'             => $eletronicPointProfile->getTolerance()
        ]);

        return $this->find($id);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }
}
