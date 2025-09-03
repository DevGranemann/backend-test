<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class InvestimentoController extends AbstractController {
    #[Route('/api/investimentos', name: 'listar_investimentos', methods: ['GET'])]
    public function index(): JsonResponse {

        return $this->json([
            'message' => 'Bem-vindo à API de Investimentos',
            'status' => 'okay',
        ]);
    }
}
