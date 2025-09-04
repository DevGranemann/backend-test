<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Investment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
class InvestimentoController extends AbstractController {
    #[Route('/api/investments', name: 'create_investments', methods: ['POST'])]
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
}
