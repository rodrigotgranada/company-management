<?php

namespace App\Controller;

use App\Entity\Socio;
use App\Entity\Empresa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/socios')]
class SocioController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('', name: 'criar_socio', methods: ['POST'])]
    public function criarSocio(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nome']) || empty($data['cpf']) || empty($data['empresaId'])) {
            return new JsonResponse(['status' => 'Dados incompletos'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $empresa = $this->entityManager->getRepository(Empresa::class)->find($data['empresaId']);
        if (!$empresa) {
            return new JsonResponse(['status' => 'Empresa não encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        $socio = new Socio();
        $socio->setNome($data['nome']);
        $socio->setCpf($data['cpf']);
        $socio->setEndereco($data['endereco'] ?? null);
        $socio->setTelefone($data['telefone'] ?? null);
        $socio->setEmpresa($empresa);

        $this->entityManager->persist($socio);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Sócio criado com sucesso'], JsonResponse::HTTP_CREATED);
    }


    #[Route('', name: 'listar_socios', methods: ['GET'])]
    public function listarSocios(): JsonResponse
    {
        $socios = $this->entityManager->getRepository(Socio::class)->findAll();
        $data = [];

        foreach ($socios as $socio) {
            $data[] = [
                'id' => $socio->getId(),
                'nome' => $socio->getNome(),
                'cpf' => $socio->getCpf(),
                'endereco' => $socio->getEndereco(),
                'telefone' => $socio->getTelefone(),
                'empresa' => [
                    'id' => $socio->getEmpresa()->getId(),
                    'nome' => $socio->getEmpresa()->getNome(),
                ],
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }


    #[Route('/{id}', name: 'atualizar_socio', methods: ['PUT'])]
    public function atualizarSocio(string $id, Request $request): JsonResponse
    {
        $id = (int) $id;
        $socio = $this->entityManager->getRepository(Socio::class)->find($id);

        if (!$socio) {
            return new JsonResponse(['status' => 'Sócio não encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nome'])) {
            $socio->setNome($data['nome']);
        }
        if (isset($data['cpf'])) {
            $socio->setCpf($data['cpf']);
        }
        if (isset($data['endereco'])) {
            $socio->setEndereco($data['endereco']);
        }
        if (isset($data['telefone'])) {
            $socio->setTelefone($data['telefone']);
        }
        if (isset($data['empresaId'])) {
            $empresa = $this->entityManager->getRepository(Empresa::class)->find($data['empresaId']);
            if (!$empresa) {
                return new JsonResponse(['status' => 'Empresa não encontrada'], JsonResponse::HTTP_NOT_FOUND);
            }
            $socio->setEmpresa($empresa);
        }

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Sócio atualizado com sucesso'], JsonResponse::HTTP_OK);
    }

 
    #[Route('/{id}', name: 'excluir_socio', methods: ['DELETE'])]
    public function excluirSocio(string $id): JsonResponse
    {
        $id = (int) $id;
        $socio = $this->entityManager->getRepository(Socio::class)->find($id);

        if (!$socio) {
            return new JsonResponse(['status' => 'Sócio não encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($socio);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Sócio excluído com sucesso'], JsonResponse::HTTP_OK);
    }
}
