<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\Socio;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/empresas')]
class EmpresaController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Criar uma nova empresa
    #[Route('', name: 'criar_empresa', methods: ['POST'])]
    public function criarEmpresa(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $empresa = new Empresa();
        $empresa->setNome($data['nome']);
        $empresa->setCnpj($data['cnpj']);
        $empresa->setEndereco($data['endereco'] ?? null);
        $empresa->setTelefone($data['telefone'] ?? null);
        $empresa->setEmail($data['email'] ?? null);

        $this->entityManager->persist($empresa);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Empresa criada com sucesso'], JsonResponse::HTTP_CREATED);
    }

    // Listar todas as empresas
    #[Route('', name: 'listar_empresas', methods: ['GET'])]
    public function listarEmpresas(): JsonResponse
    {
        $empresas = $this->entityManager->getRepository(Empresa::class)->findAll();
        $data = [];

        foreach ($empresas as $empresa) {
            $data[] = [
                'id' => $empresa->getId(),
                'nome' => $empresa->getNome(),
                'cnpj' => $empresa->getCnpj(),
                'endereco' => $empresa->getEndereco(),
                'telefone' => $empresa->getTelefone(),
                'email' => $empresa->getEmail(),
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    // Atualizar uma empresa existente
    #[Route('/{id}', name: 'atualizar_empresa', methods: ['PUT'])]
    public function atualizarEmpresa(string $id, Request $request): JsonResponse
    {
        $id = (int) $id; // Converte o valor para inteiro

        $empresa = $this->entityManager->getRepository(Empresa::class)->find($id);

        if (!$empresa) {
            return new JsonResponse(['status' => 'Empresa não encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        $empresa->setNome($data['nome'] ?? $empresa->getNome());
        $empresa->setCnpj($data['cnpj'] ?? $empresa->getCnpj());
        $empresa->setEndereco($data['endereco'] ?? $empresa->getEndereco());
        $empresa->setTelefone($data['telefone'] ?? $empresa->getTelefone());
        $empresa->setEmail($data['email'] ?? $empresa->getEmail());

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Empresa atualizada com sucesso'], JsonResponse::HTTP_OK);
    }

    // Excluir uma empresa
    #[Route('/{id}', name: 'excluir_empresa', methods: ['DELETE'])]
    public function excluirEmpresa(string $id): JsonResponse
    {
        $id = (int) $id; // Converte o valor para inteiro

        $empresa = $this->entityManager->getRepository(Empresa::class)->find($id);

        if (!$empresa) {
            return new JsonResponse(['status' => 'Empresa não encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Verificar se há sócios associados à empresa
        $socios = $this->entityManager->getRepository(Socio::class)->findBy(['empresa' => $empresa]);
        if (count($socios) > 0) {
            return new JsonResponse(['status' => 'Empresa não pode ser excluída. Existem sócios associados.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->remove($empresa);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Empresa excluída com sucesso'], JsonResponse::HTTP_OK);
    }

    // Listar todos os sócios de uma empresa
    #[Route('/{id}/socios', name: 'listar_socios_empresa', methods: ['GET'])]
    public function listarSociosDaEmpresa(string $id): JsonResponse
    {
        $id = (int) $id; // Converte o valor para inteiro

        $empresa = $this->entityManager->getRepository(Empresa::class)->find($id);

        if (!$empresa) {
            return new JsonResponse(['status' => 'Empresa não encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        $socios = $this->entityManager->getRepository(Socio::class)->findBy(['empresa' => $empresa]);
        $data = [];

        foreach ($socios as $socio) {
            $data[] = [
                'id' => $socio->getId(),
                'nome' => $socio->getNome(),
                'cpf' => $socio->getCpf(),
                'endereco' => $socio->getEndereco(),
                'telefone' => $socio->getTelefone(),
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
