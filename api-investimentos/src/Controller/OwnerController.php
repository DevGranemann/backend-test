<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;

class OwnerController extends AbstractController
{
    // =========================
    // POST /api/clientowner/create
    // =========================
    #[Route('/api/clientowner/create', name: 'create_owner', methods: ['POST'])]
    #[OA\Post(
        path: '/api/clientowner/create',
        summary: 'Cria um novo proprietário',
        description: 'Cria um proprietário (Owner) com um nome informado.',
        tags: ['Owners']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Dados do proprietário',
        content: new OA\JsonContent(
            required: ['ownerName'],
            properties: [
                new OA\Property(
                    property: 'ownerName',
                    type: 'string',
                    example: 'João Silva',
                    description: 'Nome do proprietário a ser criado'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Proprietário criado com sucesso',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Proprietário criado com sucesso.'),
                new OA\Property(
                    property: 'owner',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'João Silva')
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Parâmetros inválidos',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Parâmetros inválidos. Informe: o nome do proprietário.')
            ]
        )
    )]
    public function createOwner(Request $request, EntityManagerInterface $em): JsonResponse
    {
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
}

