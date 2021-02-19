<?php

namespace App\Controller;

use App\Entity\Films;
use App\Repository\FilmsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AccueilController extends AbstractController
{
    /**
     * @Route("/accueil", name="accueil")
     */
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }

    protected function serializeJson($objet)
    {
        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getNom();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [$encoder]);
        $jsonContent = $serializer->serialize($objet, 'json');
        return $jsonContent;
    }

    /**
     * @Route("/api/createFilms", name="createFilms")
     * @param Request $request
     * @param FilmsRepository $filmsRepository
     * @return JsonResponse
     */
    public function createFilms(Request $request, FilmsRepository $filmsRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $films = new Films();
        $data = json_decode($request->request->getContent(), true);
        $films->setNom($data['Nom'])
            ->setSynopsys($data['Synopsys'])
            ->setType($data['Type']);
        $entityManager->persist($films);
        $entityManager->flush();
        return JsonResponse::fromJsonString(($this->serializeJson($films, Response::HTTP_OK)));
    }
}
