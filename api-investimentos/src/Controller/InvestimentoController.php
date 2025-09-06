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
use App\Repository\InvestmentRepository;

class InvestimentoController extends AbstractController {

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
    public function investmentList(Request $request, int $ownerId, EntityManagerInterface $em, InvestmentRepository $investmentRepository): JsonResponse {

        $owner = $em->getRepository(\App\Entity\Owner::class)->find($ownerId);

        if (!$owner) {
            return $this->json([
                'message' => 'Proprietário não encontrado.'
            ], 404);
        }

        if (count($owner->getInvestments()) === 0) {
            return $this->json([
                'message' => 'Nenhum investimento encontrado para este proprietário.'
            ], 404);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10))); // proteção

        $paginated = $investmentRepository->findPaginatedByOwner($owner, $page, $limit);
        $investments = $paginated['items'];
        $total = $paginated['total'];
        $pages = $paginated['pages'];

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
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $pages,
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
    // Lista de ganhos de investimentos que foram retirados (por proprietário)
    #[Route('/api/investments/withdrawn-gains/{ownerId}', name: 'list_withdrawn_gains', methods: ['GET'])]
    public function listWithdrawnGains(int $ownerId, EntityManagerInterface $em): JsonResponse
    {
        $owner = $em->getRepository(\App\Entity\Owner::class)->find($ownerId);

        if (!$owner) {
            return $this->json(['error' => 'Proprietário não encontrado.'], 404);
        }

        $investments = $em->getRepository(Investment::class)->findBy([
            'owner' => $owner,
            'withdrawnAt' => ['not' => null]
        ]);

        if (!$investments) {
        return $this->json([
            'error' => 'Nenhum investimento encontrado'
            ], 404);
        }

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

    // lista o saldo futuro de um investimento
    #[Route('/api/investments/future-balances/{investmentId}', name: 'investment_future_balances', methods: ['GET'])]
    public function projectFutureBalances(int $investmentId, Request $request, EntityManagerInterface $em): JsonResponse {

        $investment = $em->getRepository(Investment::class)->find($investmentId);

        if (!$investment) {
            return $this->json(['error' => 'Investimento não encontrado.'], 404);
        }

        $years = max(1, (int) $request->query->get('years', 3));

        $projection = InvestmentCalculator::projectFutureBalances($investment, 3);

        return $this->json([
            'investmentId' => $investment->getId(),
            'ownerId' => $investment->getOwner()->getId(),
            'ownerName' => $investment->getOwner()->getName(),
            'years' => $years,
            'projections' => $projection
        ]);
    }
}
