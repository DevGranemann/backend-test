<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Investment;
use App\Entity\Owner;
use App\Repository\InvestmentRepository;
use App\Utils\InvestmentCalculator;
use App\Utils\TakeInvestmentOut;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;

class InvestimentoController extends AbstractController
{
    // =========================
    // POST /api/investments/create
    // =========================
    #[Route('/api/investments/create', name: 'create_investments', methods: ['POST'])]
    #[OA\Post(
        path: '/api/investments/create',
        summary: 'Cria um novo investimento',
        description: 'Cria um investimento para um proprietário existente com data e valor.',
        tags: ['Investments']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Dados do investimento',
        content: new OA\JsonContent(
            required: ['ownerId', 'creationDate', 'investmentValue'],
            properties: [
                new OA\Property(property: 'ownerId', type: 'integer', example: 1, description: 'ID do proprietário'),
                new OA\Property(property: 'creationDate', type: 'string', format: 'date-time', example: '2025-09-06T00:00:00', description: 'Data de criação do investimento'),
                new OA\Property(property: 'investmentValue', type: 'number', format: 'float', example: 1000.50, description: 'Valor do investimento')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Investimento criado com sucesso')]
    #[OA\Response(response: 400, description: 'Parâmetros inválidos')]
    #[OA\Response(response: 404, description: 'Proprietário não encontrado')]
    public function createInvest(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['ownerId'], $data['creationDate'], $data['investmentValue'])) {
            return $this->json([
                'error' => 'Parâmetros inválidos. Informe: ownerId, creationDate e investmentValue.'
            ], 400);
        }

        $owner = $em->getRepository(Owner::class)->find($data['ownerId']);
        if (!$owner) {
            return $this->json(['error' => 'Proprietário não encontrado.'], 404);
        }

        $investment = new Investment();
        $investment->setOwner($owner);
        $investment->setCreationDate(new \DateTime($data['creationDate']));
        $investment->setInvestmentValue((float)$data['investmentValue']);

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

    // =========================
    // GET /api/investments/list/{ownerId}
    // =========================
    #[Route('/api/investments/list/{ownerId}', name: 'list_investments', methods: ['GET'])]
    #[OA\Get(
        path: '/api/investments/list/{ownerId}',
        summary: 'Lista investimentos de um proprietário',
        description: 'Retorna a lista de investimentos de um proprietário com paginação.',
        tags: ['Investments']
    )]
    #[OA\Parameter(name: 'ownerId', in: 'path', required: true, description: 'ID do proprietário', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Número da página', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Itens por página', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Lista de investimentos retornada com sucesso')]
    #[OA\Response(response: 404, description: 'Proprietário não encontrado ou sem investimentos')]
    public function investmentList(Request $request, int $ownerId, EntityManagerInterface $em, InvestmentRepository $investmentRepository): JsonResponse
    {
        $owner = $em->getRepository(Owner::class)->find($ownerId);
        if (!$owner) {
            return $this->json(['message' => 'Proprietário não encontrado.'], 404);
        }

        if (count($owner->getInvestments()) === 0) {
            return $this->json(['message' => 'Nenhum investimento encontrado.'], 404);
        }

        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(50, max(1, (int)$request->query->get('limit', 10)));

        $paginated = $investmentRepository->findPaginatedByOwner($owner, $page, $limit);
        $investments = $paginated['items'];
        $result = [];

        foreach ($investments as $investment) {
            $result[] = [
                'id' => $investment->getId(),
                'ownerId' => $owner->getId(),
                'ownerName' => $owner->getName(),
                'creationDate' => $investment->getCreationDate()->format('Y-m-d H:i:s'),
                'investmentValue' => $investment->getInvestmentValue(),
                'valueWithWinnings' => InvestmentCalculator::calculateInvestment($investment),
                'winningsValueOnly' => InvestmentCalculator::calculateValueWinnings($investment),
            ];
        }

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $paginated['total'],
            'pages' => $paginated['pages'],
            'investments' => $result
        ]);
    }

    // =========================
    // PUT /api/investments/draw/{id}
    // =========================
    #[Route('/api/investments/draw/{id}', name: 'draw_investments', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/investments/draw/{id}',
        summary: 'Saca um investimento',
        description: 'Realiza o saque de um investimento específico.',
        tags: ['Investments']
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID do investimento', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Saque realizado com sucesso')]
    #[OA\Response(response: 400, description: 'Investimento não encontrado ou erro no saque')]
    public function drawInvestmentAccount(int $id, EntityManagerInterface $em): JsonResponse
    {
        $investment = $em->getRepository(Investment::class)->find($id);
        if (!$investment) {
            return $this->json(['error' => 'Investimento não encontrado'], 400);
        }

        try {
            $withdrawValue = TakeInvestmentOut::TakeOutInvestment($investment);
            $em->persist($investment);
            $em->flush();
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
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

    // =========================
    // GET /api/investments/withdrawn-gains/{ownerId}
    // =========================
    #[Route('/api/investments/withdrawn-gains/{ownerId}', name: 'list_withdrawn_gains', methods: ['GET'])]
    #[OA\Get(
        path: '/api/investments/withdrawn-gains/{ownerId}',
        summary: 'Lista ganhos retirados de um proprietário',
        description: 'Retorna todos os ganhos que já foram retirados por um proprietário.',
        tags: ['Investments']
    )]
    #[OA\Parameter(name: 'ownerId', in: 'path', required: true, description: 'ID do proprietário', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Lista de ganhos retirados retornada com sucesso')]
    #[OA\Response(response: 404, description: 'Proprietário ou ganhos não encontrados')]
    public function listWithdrawnGains(int $ownerId, EntityManagerInterface $em, InvestmentRepository $investmentRepository): JsonResponse
    {
        $owner = $em->getRepository(Owner::class)->find($ownerId);
        if (!$owner) {
            return $this->json(['error' => 'Proprietário não encontrado.'], 404);
        }

        $investments = $investmentRepository->findWithdrawnByOwner($owner);
        if (count($investments) === 0) {
            return $this->json(['error' => 'Nenhum investimento retirado encontrado'], 404);
        }

        $result = [];
        foreach ($investments as $investment) {
            $result[] = [
                'investmentId' => $investment->getId(),
                'withdrawnAt' => $investment->getWithdrawnAt()?->format('Y-m-d H:i:s'),
                'profit' => $investment->getWithdrawnGain(),
                'ownerId' => $owner->getId(),
                'ownerName' => $owner->getName()
            ];
        }

        return $this->json([
            'withdrawnGains' => $result,
            'total' => count($investments)
        ]);
    }

    // =========================
    // GET /api/investments/future-balances/{investmentId}
    // =========================
    #[Route('/api/investments/future-balances/{investmentId}', name: 'investment_future_balances', methods: ['GET'])]
    #[OA\Get(
        path: '/api/investments/future-balances/{investmentId}',
        summary: 'Projeta saldos futuros de um investimento',
        description: 'Calcula o saldo futuro do investimento ao longo de anos especificados.',
        tags: ['Investments']
    )]
    #[OA\Parameter(name: 'investmentId', in: 'path', required: true, description: 'ID do investimento', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'years', in: 'query', required: false, description: 'Número de anos para projeção', schema: new OA\Schema(type: 'integer', default: 3))]
    #[OA\Response(response: 200, description: 'Projeção retornada com sucesso')]
    #[OA\Response(response: 404, description: 'Investimento não encontrado')]
    public function projectFutureBalances(int $investmentId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $investment = $em->getRepository(Investment::class)->find($investmentId);
        if (!$investment) {
            return $this->json(['error' => 'Investimento não encontrado.'], 404);
        }

        $years = max(1, (int)$request->query->get('years', 3));
        $projection = InvestmentCalculator::projectFutureBalances($investment, $years);

        return $this->json([
            'investmentId' => $investment->getId(),
            'ownerId' => $investment->getOwner()->getId(),
            'ownerName' => $investment->getOwner()->getName(),
            'years' => $years,
            'projections' => $projection
        ]);
    }
}

