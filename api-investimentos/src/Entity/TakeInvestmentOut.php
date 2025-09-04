<?php

namespace App\Utils;

use App\Entity\Investment;

class TakeInvestmentOut {
    public static function TakeOutInvestment(Investment $investment): float {
        $balance = $investment->getInvestmentValue();
        $ownerName = $investment->getOwner();

        if ($balance == 0) {
            throw new \InvalidArgumentException('Não há saldo nesta conta');
        }

        $investment->setInvestmentValue(0);

        return $balance;
    }
}
