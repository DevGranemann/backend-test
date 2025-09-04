<?php

namespace App\Utils;

use App\Entity\Investment;

class InvestmentCalculator
{
    public static function calculateInvestment(Investment $investment): float
    {
        $initValue = $investment->getInvestmentValue();
        $creationDate = $investment->getCreationDate();

        if (empty($creationDate)) {
            throw new \InvalidArgumentException('Não é possível calcular o valor: data de criação nula.');
        }

        if (!$creationDate instanceof \DateTime) {
            try {
                $creationDate = new \DateTime($creationDate);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Formato de data de criação inválido.');
            }
        }

        $currentDate = new \DateTime();
        $interval = $creationDate->diff($currentDate);
        $months = ($interval->y * 12) + $interval->m;

        $finalValue = $initValue * pow(1.0052, $months);

        return round($finalValue, 2);
    }
}
