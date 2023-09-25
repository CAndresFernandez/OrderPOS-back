<?php

namespace App\Controller\API;

use App\Entity\ClosedOrder;
use App\Repository\ClosedOrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ClosedOrderController extends AbstractController
{
    /**
     * @Route("api/closed/{id}", name="app_api_closed_show", methods={"GET"})
     */
    public function show(ClosedOrder $closedOrder): JsonResponse
    {
        // on retour les closedOrders en json
        return $this->json($closedOrder, Response::HTTP_OK, [], ["groups" => "closed"]);
    }

    /**
     * @Route("/api/closed", name="app_api_closed_list")
     */
    public function list(ClosedOrderRepository $closedOrderRepository): JsonResponse
    {
        //  récupérer les categories
        $closedOrders = $closedOrderRepository->findAll();

        // on retour les catégories en json
        return $this->json($closedOrders, Response::HTTP_OK);
    }

    /**
     * @Route("/api/closed/new", name="app_api_closed_add", methods={"POST"})
     */
    public function add(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, ClosedOrderRepository $closedOrderRepository): JsonResponse
    {
        // ICI je récupère le contenu de la requête à ce stade c'est du json 
        $jsonContent = $request->getContent();
        // J'ai besoin d'une entité pour faire l'ajout en bdd donc je transforme le json en entité à l'aide du serializer
        // la méthode veut dire ce contenu, tu le transforme en Movie, le contenu de base est du json.
        
        // mettre un try catch au cas ou le json n'est pas bon
        try {
            $closedOrder = $serializer->deserialize($jsonContent, ClosedOrder::class, 'json');
            
        } catch (NotEncodableValueException $e) {
            // si je suis ici c'est que le json n'est pas bon
            return $this->json(["error" => "json invalide"], Response::HTTP_BAD_REQUEST);
        }

        // je check si mon film contient des erreurs
        $errors = $validator->validate($closedOrder);
        
        // est ce qu'il y a au moins une erreur
        if (count($errors) > 0) {

            foreach ($errors as $error) {
                // je me crée un tableau avec les erreurs en valeur et les champs concernés en index
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json($dataErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // ! j'arrive je sais que mes constraints sont bien passés
        $closedOrderRepository->add($closedOrder, true);

        // on retour les films en json
        // préférence perso : je retourne le film crée
        // norme rest : 201, Location avec le lien de la ressource
        return $this->json($closedOrder, Response::HTTP_CREATED, ["Location" => $this->generateUrl("app_api_closed_show", ["id" => $closedOrder->getId()])]);
    }
}
