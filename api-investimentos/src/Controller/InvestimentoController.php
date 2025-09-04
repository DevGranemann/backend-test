<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Investment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
class InvestimentoController extends AbstractController {
    #[Route('/api/investments/create', name: 'create_investments', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['owner'], $data['creationDate'], $data['investmentValue'])) {

            return $this->json([
                'error' => 'Parâmetros inválidos. Informe: nome do proprietário (owner), a data da criação do investimento (creationDate) e o valor do investimento (investmentValue).'
            ], 400);
        }

        $investment = new Investment();
        $investment->setOwner($data['owner']);
        $investment->setCreationDate(new \DateTime($data['creationDate']));
        $investment->setInvestmentValue((float) $data['investmentValue']);

        $em->persist($investment);
        $em->flush();

        return $this->json([
            'message' => 'Investimento criado com sucesso.',
            'Investment' => [
                'id' => $investment->getId(),
                'ownerName' => $investment->getOwner(),
                'creationDate' => $investment->getCreationDate()->format('Y-m-d H:i:s'),
                'investmentValue' => $investment->getInvestmentValue(),
            ]
        ], 201);
    }

    #[Route('/api/investments/list/{id}', name: 'list_investments', methods: ['GET'])]
    public function investmentList(int $id, EntityManagerInterface $em): JsonResponse {

        $investments = $em->getRepository(Investment::class)->findBy(['id' => $id]);

        if (!$investments) {
            return $this->json([
                'message' => 'Nenhum investimento encontrado para este propritário.'
            ], 404);
        }

        $result = [];
        foreach ($investments as $investment) {
            $valueWinnings = $this->calculateInvestment($investment);
            $result[] = [
                'id' => $investment->getId(),
                'owner' => $investment->getOwner(),
                'creationDate' => $investment->getCreationDate(),
                'investmentValue' => $investment->getInvestmentValue(),
                'valueWithWinnings' => $valueWinnings,
            ];
        }

        return $this->json([
            'investments' => $result
        ]);
    }

    /*
        Calcula o valor dos ganhos do investimento com o acrécimo de 0,52%/mes
    */

    private function calculateInvestment(Investment $investment): float {

        $initValue = $investment->getInvestmentValue();
        $creationDate = $investment->getCreationDate();
        $currentDate = new \DateTime();

        $interval = $creationDate->diff($currentDate);
        $months = ($interval->y * 12) + $interval->m;

        $finalValue = $initValue * pow(1.0052, $months);

        return round($finalValue, 2);
    }
}
