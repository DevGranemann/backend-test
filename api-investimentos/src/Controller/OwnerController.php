<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OwnerController extends AbstractController {

    #[Route('/api/clientowner/create', name: 'create_owner', methods: ['POST'])]
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
}
