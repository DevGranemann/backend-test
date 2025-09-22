<?php

namespace App\Service;

class DateCalculator {

    public function calculateMonthsBetween(\DateTime $start, \DateTime $end): int{
        return $start->diff($end)->m + ($start->diff($end)->y * 12);
    }
}
