<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Investment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\InvestmentCalculator;
use App\Utils\TakeInvestmentOut;

class InvestimentoController extends AbstractController {

    #[Route('/api/clientOwner/create', name: 'create_owner', methods: ['POST'])]
    public function createOwner(Request $request, EntityManagerInterface $em): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['ownerName'])) {
            return $this->json([
                'error' => 'Parâmetros inválidos. Informe: o nome do proprietário.'
            ], 400);
        }

        $owner = new \App\Entity\Owner();
        $owner->setName($data['ownerName']);

        $em->persist($owner);
        $em->flush();

        return $this->json([
            'message' => 'Proprietário criado com sucesso.',
            'owner' => [
                'id' => $owner->getId(),
                'name' => $owner->getName()
            ]
        ], 201);
    }

    #[Route('/api/investments/create', name: 'create_investments', methods: ['POST'])]
    public function createInvest(Request $request, EntityManagerInterface $em): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['ownerId'], $data['creationDate'], $data['investmentValue'])) {
            return $this->json([
                'error' => 'Parâmetros inválidos. Informe: ID do proprietário (ownerId), data da criação do investimento (creationDate) e valor do investimento (investmentValue).'
            ], 400);
        }

        // Busca o Owner pelo ID
        $owner = $em->getRepository(\App\Entity\Owner::class)->find($data['ownerId']);
        if (!$owner) {
            return $this->json([
                'error' => 'Proprietário não encontrado.'
            ], 404);
        }

        $investment = new Investment();
        $investment->setOwner($owner);
        $investment->setCreationDate(new \DateTime($data['creationDate']));
        $investment->setInvestmentValue((float) $data['investmentValue']);

        $em->persist($investment);
        $em->flush();

        return $this->json([
            'message' => 'Investimento criado com sucesso.',
            'Investment' => [
                'id' => $investment->getId(),
                'ownerId' => $owner->getId(),
                'ownerName' => $owner->getName(),
                'creationDate' => $investment->getCreationDate()->format('Y-m-d H:i:s'),
                'investmentValue' => $investment->getInvestmentValue(),
            ]
        ], 201);
    }

    #[Route('/api/investments/list/{ownerId}', name: 'list_investments', methods: ['GET'])]
    public function investmentList(int $ownerId, EntityManagerInterface $em): JsonResponse {

        $owner = $em->getRepository(\App\Entity\Owner::class)->find($ownerId);

        if (!$owner) {
            return $this->json([
                'message' => 'Proprietário não encontrado.'
            ], 404);
        }

        $investments = $owner->getInvestments();

        if (count($investments) === 0) {
            return $this->json([
                'message' => 'Nenhum investimento encontrado para este proprietário.'
            ], 404);
        }

        $result = [];
        foreach ($investments as $investment) {
            $valueWinnings = InvestmentCalculator::calculateInvestment($investment);
            $valueWinningsOnly = InvestmentCalculator::calculateValueWinnings($investment);
            $result[] = [
                'id' => $investment->getId(),
                'ownerId' => $owner->getId(),
                'ownerName' => $owner->getName(),
                'creationDate' => $investment->getCreationDate()->format('Y-m-d H:i:s'),
                'investmentValue' => $investment->getInvestmentValue(),
                'valueWithWinnings' => $valueWinnings,
                'winningsValueOnly' => $valueWinningsOnly,
            ];
        }

        return $this->json([
            'investments' => $result
        ]);
    }

    #[Route('/api/investments/draw/{id}', name: 'draw_investments', methods: ['PUT'])]
    public function drawInvestmentAccount(int $id, EntityManagerInterface $em): JsonResponse {
        $investment = $em->getRepository(Investment::class)->find($id);

        if (!$investment) {
            return $this->json([
                'error' => 'Investimento não encontrado'
            ], 400);
        }

        try {
            $withdrawValue = TakeInvestmentOut::TakeOutInvestment($investment);
            $em->persist($investment);
            $em->flush();
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        }

        $owner = $investment->getOwner();

        return $this->json([
            'message' => 'Saque realizado com sucesso',
            'withdrawValue' => $withdrawValue,
            'investmentId' => $investment->getId(),
            'ownerId' => $owner ? $owner->getId() : null,
            'ownerName' => $owner ? $owner->getName() : null
        ]);
    }
    
    #[Route('/api/investments/withdrawn-gains/{ownerId}', name: 'list_withdrawn_gains', methods: ['GET'])]
    public function listWithdrawnGains(int $ownerId, EntityManagerInterface $em): JsonResponse
    {
        $owner = $em->getRepository(\App\Entity\Owner::class)->find($ownerId);
        if (!$owner) {
            return $this->json(['error' => 'Proprietário não encontrado.'], 404);
        }

        $investments = $em->getRepository(Investment::class)->findBy([
            'owner' => $owner,
            // Considera apenas investimentos já retirados
            'withdrawnAt' => ['not' => null]
        ]);

        $result = [];
        foreach ($investments as $investment) {
            $gain = InvestmentCalculator::calculateValueWinnings($investment);
            $result[] = [
                'investmentId' => $investment->getId(),
                'withdrawnAt' => $investment->getWithdrawnAt()?->format('Y-m-d H:i:s'),
                'gain' => $gain
            ];
        }

        return $this->json(['withdrawnGains' => $result]);
    }
}
