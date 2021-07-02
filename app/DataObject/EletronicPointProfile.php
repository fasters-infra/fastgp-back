<?php

namespace App\DataObject;

class EletronicPointProfile
{

    private $title;
    private $entryTime;
    private $breakTime;
    private $intervalReturnTime;
    private $departureTime;
    private $tolerance;

    function __construct(
        string $title,
        string $entryTime,
        string $breakTime,
        string $intervalReturnTime,
        string $departureTime
    ) {
        $this->title = $title;
        $this->entryTime = $entryTime;
        $this->breakTime = $breakTime;
        $this->intervalReturnTime = $intervalReturnTime;
        $this->departureTime = $departureTime;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): EletronicPointProfile
    {
        $this->title = $title;

        return $this;
    }

    public function getEntryTime(): string
    {
        return $this->entryTime;
    }

    public function setEntryTime(string $entryTime): EletronicPointProfile
    {
        $this->entryTime = $entryTime;

        return $this;
    }

    public function getBreakTime(): string
    {
        return $this->breakTime;
    }

    public function setBreakTime(string $breakTime): EletronicPointProfile
    {
        $this->breakTime = $breakTime;

        return $this;
    }

    public function getIntervalReturnTime(): string
    {
        return $this->intervalReturnTime;
    }

    public function setIntervalReturnTime(string $intervalReturnTime): EletronicPointProfile
    {
        $this->intervalReturnTime = $intervalReturnTime;

        return $this;
    }

    public function getDepartureTime(): string
    {
        return $this->departureTime;
    }

    public function setDepartureTime(string $departureTime): EletronicPointProfile
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getTolerance(): int
    {
        return $this->tolerance;
    }

    public function setTolerance(int $tolerance): EletronicPointProfile
    {
        $this->tolerance = $tolerance;

        return $this;
    }
}
