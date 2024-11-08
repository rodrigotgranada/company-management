<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\Socio;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/empresas')]
class EmpresaController extends AbstractController
{
    private $entityManager;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    
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

 
        $errors = $this->validator->validate($empresa);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['status' => 'Erro de validação', 'errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }


        $this->entityManager->persist($empresa);
        $this->entityManager->flush();


        return new JsonResponse(
            [
                'status' => 'Empresa criada com sucesso',
                'id' => $empresa->getId(),
                'nome' => $empresa->getNome(),
                'cnpj' => $empresa->getCnpj(),
                'endereco' => $empresa->getEndereco(),
                'telefone' => $empresa->getTelefone(),
                'email' => $empresa->getEmail(),
            ],
            JsonResponse::HTTP_CREATED
        );
    }


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


    #[Route('/{id}', name: 'obter_empresa', methods: ['GET'])]
    public function obterEmpresa(string $id): JsonResponse
    {
        $id = (int) $id;

        $empresa = $this->entityManager->getRepository(Empresa::class)->find($id);

        if (!$empresa) {
            return new JsonResponse(['status' => 'Empresa não encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $empresa->getId(),
            'nome' => $empresa->getNome(),
            'cnpj' => $empresa->getCnpj(),
            'endereco' => $empresa->getEndereco(),
            'telefone' => $empresa->getTelefone(),
            'email' => $empresa->getEmail(),
        ];

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

 
   #[Route('/{id}', name: 'atualizar_empresa', methods: ['PUT'])]
   public function atualizarEmpresa(string $id, Request $request): JsonResponse
   {
       $id = (int) $id;

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

   
       $errors = $this->validator->validate($empresa);
       if (count($errors) > 0) {
           $errorMessages = [];
           foreach ($errors as $error) {
               $errorMessages[] = $error->getMessage();
           }
           return new JsonResponse(['status' => 'Erro de validação', 'errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
       }

    
       $this->entityManager->flush();

       return new JsonResponse(['status' => 'Empresa atualizada com sucesso'], JsonResponse::HTTP_OK);
   }


    #[Route('/{id}', name: 'excluir_empresa', methods: ['DELETE'])]
    public function excluirEmpresa(string $id): JsonResponse
    {
        $id = (int) $id;

        $empresa = $this->entityManager->getRepository(Empresa::class)->find($id);

        if (!$empresa) {
            return new JsonResponse(['status' => 'Empresa não encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

   
        $socios = $this->entityManager->getRepository(Socio::class)->findBy(['empresa' => $empresa]);
        if (count($socios) > 0) {
            return new JsonResponse(['status' => 'Empresa não pode ser excluída. Existem sócios associados.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->remove($empresa);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Empresa excluída com sucesso'], JsonResponse::HTTP_OK);
    }


    #[Route('/{id}/socios', name: 'listar_socios_empresa', methods: ['GET'])]
    public function listarSociosDaEmpresa(string $id): JsonResponse
    {
        $id = (int) $id;

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
