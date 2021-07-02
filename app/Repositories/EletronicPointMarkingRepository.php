<?php

namespace App\Repositories;

use App\Models\EletronicPointMarking;
use Carbon\Carbon;

class EletronicPointMarkingRepository
{
    private $model;

    function __construct(EletronicPointMarking $model)
    {
        $this->model = $model;
    }

    public function mark(int $userId, UserRepository $userRepository)
    {
        $userProfile = $userRepository->eletronicPointProfile($userId);

        $countMarkation = $this->model
            ->where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($countMarkation >= 4) {
            return;
        }

        if (empty($userProfile) || empty($userProfile->tolerance)) {
            return $this->model->create([
                'need_justification' => false,
                'user_id' => $userId,
            ]);
        }

        return $this->model->create([
            'need_justification' => $this->verifyMarcation($userProfile),
            'user_id' => $userId,
        ]);
    }

    public function justify(int $id, int $justifiedBy, string $justification)
    {
        $markation = $this->model->find($id);

        if (empty($markation)) {
            return;
        }

        $markation->justified_by = $justifiedBy;
        $markation->justification = $justification;

        return $markation->save();
    }

    public function period(string $startDate, string $endDate, int $userId)
    {
        $from = date($startDate);
        $to = date($endDate);

        $periods = $this->model->whereBetween('created_at', [$from, $to])->where('user_id', $userId)->orderBy('created_at')->get();
        $returnPeriodFormated = [];

        foreach ($periods as $period) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $period->created_at);
            $returnPeriodFormated[$date->toDateString()] = $returnPeriodFormated[$date->toDateString()] ?? [];
            array_push($returnPeriodFormated[$date->toDateString()], $period);
        }

        return $returnPeriodFormated;
    }

    private function verifyMarcation($profile)
    {
        $carbonNow = Carbon::now();
        $carbonNow->addHour(-3);

        $actualHour = $carbonNow->hour;
        $actualMinute = $carbonNow->minute;

        list($hourEntryTime, $minuteEntryTime) = explode(':', $profile->entry_time);
        list($hourBreakTime, $minuteBreakTime) = explode(':', $profile->break_time);
        list($hourIntervalReturnTime, $minuteIntervalReturnTime) = explode(':', $profile->interval_return_time);
        list($hourDepartureTime, $minuteDepartureTime) = explode(':', $profile->departure_time);

        if ($actualHour >= $hourEntryTime && $actualHour < $hourBreakTime) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))
                ->setHour($hourEntryTime)
                ->setMinute($minuteEntryTime);
            return $this->needJustification($profile->tolerance, $date, $actualHour, $actualMinute);
        }

        if ($actualHour >= $hourBreakTime && $actualHour < $hourIntervalReturnTime) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))
                ->setHour($hourBreakTime)
                ->setMinute($minuteBreakTime);
            return $this->needJustification($profile->tolerance, $date, $actualHour, $actualMinute);
        }

        if ($actualHour >= $hourIntervalReturnTime && $actualHour < $hourDepartureTime) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))
                ->setHour($hourIntervalReturnTime)
                ->setMinute($minuteIntervalReturnTime);
            return $this->needJustification($profile->tolerance, $date, $actualHour, $actualMinute);
        }

        if ($actualHour >= $hourDepartureTime) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))
                ->setHour($hourDepartureTime)
                ->setMinute($minuteDepartureTime);
            return $this->needJustification($profile->tolerance, $date, $actualHour, $actualMinute);
        }

        return false;
    }

    private function needJustification($tolerance, Carbon $initialTime, $actualHour, $actualMinute)
    {
        $maxHourLimited = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))
            ->setHour($actualHour)
            ->setMinute($actualMinute)
            ->addMinutes($tolerance);

        $minHourLimited = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'))
            ->setHour($actualHour)
            ->setMinute($actualMinute)
            ->addMinutes($tolerance * -1);

        if ($initialTime >= $minHourLimited && $maxHourLimited >= $initialTime) {
            return false;
        }
        return true;
    }
}
