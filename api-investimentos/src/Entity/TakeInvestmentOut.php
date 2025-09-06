<?php

namespace App\Utils;

use App\Entity\Investment;
use App\Utils\InvestmentCalculator;

class TakeInvestmentOut {

    public static function calculateTaxDiscount(float $initValue, float $profit, Investment $investment): float {
        $creationDate = $investment->getCreationDate();
        $currentDate = new \DateTime();
        $ageInvestment = InvestmentCalculator::calculateMonthsBetween($creationDate, $currentDate);

        if ($ageInvestment < 12) {
            $taxDiscount = 0.225; // 22,5%
        } elseif ($ageInvestment >= 12 && $ageInvestment <= 24) {
            $taxDiscount = 0.185; // 18,5%
        } else {
            $taxDiscount = 0.15; // 15%
        }

        // Aplica o desconto de imposto APENAS sobre o lucro
        $tax = $profit * $taxDiscount;
        $finalValue = $initValue + ($profit - $tax);

        return $finalValue;
    }

    public static function TakeOutInvestment(Investment $investment): float {

        $initValue = $investment->getInvestmentValue();
        $updatedValue = InvestmentCalculator::calculateInvestment($investment);
        $profit = $updatedValue - $initValue;

        if ($updatedValue <= 0) {
            throw new \Exception('Não há saldo disponível para saque.');
        }

        $finalValue = self::calculateTaxDiscount($initValue, $profit, $investment);

        $investment->setInvestmentValue(0);
        $investment->setWithdrawnAt(new \DateTime());

        return round($finalValue, 2);
    }
}
