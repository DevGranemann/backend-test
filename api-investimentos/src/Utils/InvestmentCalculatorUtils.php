<?php

namespace App\Utils;

use App\Entity\Investment;

class InvestmentCalculatorUtils
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

        $currentDate = new \DateTime(); // passar no controller
        $months = self::calculateMonthsBetween($creationDate, $currentDate);

        $finalValue = $initValue * pow(1.0052, $months);

        return round($finalValue, 2);
    }

    // Calculo de meses
    public static function calculateMonthsBetween(\DateTime $start, \DateTime $end): int {
        $interval = $start->diff($end);
        return ($interval->y * 12) + $interval->m;
    }

    public static function calculateValueWinnings(Investment $investment): float {

        $initValue = $investment->getInvestmentValue();
        $finalValue = InvestmentCalculatorUtils::calculateInvestment($investment);

        $valueWinnings = $finalValue - ($initValue);

        return round($valueWinnings, 2);
    }

    public static function projectFutureBalances(Investment $investment, int $years = 3): array{
        $initValue = $investment->getInvestmentValue();
        $creationDate = $investment->getCreationDate();
        $result = [];

        for ($i = 0; $i <= $years; $i++) {
            $months = $i * 12;
            $futureValue = $initValue * pow(1.0052, $months);
            $futureDate = (clone $creationDate)->modify("+$months months")->format('Y-m-d');

            $result[] = [
                'year' => $i,
                'date' => $futureDate,
                'expectedBalance' => round($futureValue, 2)
            ];
        }

        return $result;
    }
}
