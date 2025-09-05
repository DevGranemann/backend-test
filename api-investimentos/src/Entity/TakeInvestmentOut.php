<?php

namespace App\Utils;

use App\Entity\Investment;
use App\Utils\InvestmentCalculator;

class TakeInvestmentOut {
    public static function TakeOutInvestment(Investment $investment): float {
        $balance = $investment->getInvestmentValue();
        $ownerName = $investment->getOwner();
        $creationDate = $investment->getCreationDate();
        $currentDate = new \DateTime();

        $ageInvestment = InvestmentCalculator::calculateMonthsBetween($creationDate, $currentDate);

        $taxDiscount = 0;

        if ($balance == 0) {
            throw new \InvalidArgumentException('Não há saldo nesta conta');
        }

        switch (true) {
            case $ageInvestment > 12:
                $taxDiscount = 0.15; // 15%
                break;
            case $ageInvestment >= 12 && $ageInvestment <= 24:
                $taxDiscount = 0.185; // 18,5%
                break;
            case $ageInvestment > 24:
                $taxDiscount = 0.225; // 22,5%
                break;
        }

        $finalValue = $balance - ($balance * $taxDiscount); // Alterar: desc feito apenas no (invest - ganho)

        $investment->setInvestmentValue(0);

        return round($finalValue, 2);

    }
}
