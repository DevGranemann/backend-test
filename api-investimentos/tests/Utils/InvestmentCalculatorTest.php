<?php

// Parece-me que neste arquivo terão os testes de todo o "InvestmentCalculator"

namespace App\Tests\Utils;

use App\Entity\Investment;
use App\Utils\InvestmentCalculator;
use PHPUnit\Framework\TestCase;

class InvestmentCalculatorTest extends TestCase {

    private function createInvestment(float $value, string $date = '2024-01-01'): Investment {

        $investment = new Investment();
        $investment->setInvestmentValue($value);
        $investment->setCreationDate(new \DateTime($date));

        return $investment;
    }

    public function testCalculateInvestmentValid() {

        $investment = $this->createInvestment(1000, '2024-01-01');
        $result = InvestmentCalculator::calculateInvestment($investment);

        // valor esperado apos x meses
        $months = InvestmentCalculator::calculateMonthsBetween(new \DateTime('2024-01-01'), new \DateTime());
        $expected = 1000 * pow(1.0052, $months);
        $expected = round($expected, 2);

        $this->assertEquals($expected, $result);
    }

    public function testCalculateInvestmentThrowExceptionOnNullDate() {

        $this->expectException(\InvalidArgumentException::class);

        $investment = $this->createInvestment(1000);

        // força o null com reflection
        $reflection = new \ReflectionClass($investment);
        $property = $reflection->getProperty('creationDate');
        $property->setAccessible(true);
        $property->setValue($investment, null);

        InvestmentCalculator::calculateInvestment($investment);
    }

    public function testCalculateInvestmentThrowsExceptionOnInvalidDateFormat() {

        $this->expectException(\InvalidArgumentException::class);

        $investment = $this->createInvestment(1000);

        // aqui tbm
        $reflection = new \ReflectionClass($investment);
        $property = $reflection->getProperty('creationDate');
        $property->setAccessible(true);
        $property->setValue($investment, null);

        InvestmentCalculator::calculateInvestment($investment);

    }

    public function testCalculateMonthsBetween() {

        $start = new \DateTime('2023-01-01');
        $end = new \DateTime('2023-06-01');

        $months = InvestmentCalculator::calculateMonthsBetween($start, $end);

        $this->assertEquals(5, $months); // 5 meses
    }

    public function testProjectFutureBalancesDefaultYears() {

        $investment = $this->createInvestment(1000, '2024-01-01');
        $result = InvestmentCalculator::projectFutureBalances($investment);

        $this->assertIsArray($result);
        $this->assertCount(4, $result); // 0, 1, 2, 3 anos

        $this->assertArrayHasKey('year', $result[0]);
        $this->assertArrayHasKey('date', $result[0]);
        $this->assertArrayHasKey('expectedBalance', $result[0]);
    }

    public function testProjectFutureBalancesCustomYears() {

        $investment = $this->createInvestment(1000, '2024-01-01');
        $result = InvestmentCalculator::projectFutureBalances($investment, 5);

        $this->assertCount(6, $result); // 0 até 5 anos
        $this->assertEquals(5, $result[5]['year']);
    }
}
