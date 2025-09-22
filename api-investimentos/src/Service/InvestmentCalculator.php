<?php

namespace App\Service;

use App\Entity\Investment;
use App\Service\DateCalculator;

class InvestmentCalculator {

    private DateCalculator $dateCalculator;
    private Investment $investment;

    public function __construct(DateCalculator $dateCalculator) {
        $this->dateCalculator = $dateCalculator;
    }

    public function calculateInvestment(Investment $investment, \DateTime $end): float {

        $start = $investment->getCreationDate();
        $initValue = $investment->getInvestmentValue();

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

        $months = $this->dateCalculator->calculateMonthsBetween($start, $end);
        $finalValue = $initValue * pow(1.0052, $months);

        return round($finalValue, 2);
    }
}
