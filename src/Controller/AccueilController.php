<?php

namespace App\Controller;

use App\Entity\Films;
use App\Entity\User;
use App\Repository\FilmsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
     * @Route("api/createFilms", name="createFilms", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function createFilms(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $films = new Films();
        $data = json_decode($request->getContent(), true);
        $films->setNom($data['nom'])
            ->setSynopsys($data['synopsys'])
            ->setType($data['type']);
        $entityManager->persist($films);
        $entityManager->flush();
        return JsonResponse::fromJsonString($this->serializeJson($films), Response::HTTP_OK);
    }

    /**
     * @Route("register", name="register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @return JsonResponse
     */
    public function register(Request $request, UserPasswordEncoderInterface $userPasswordEncoder){
        $entityManager = $this->getDoctrine()->getManager();
        $user = new User();
        $data = json_decode($request->getContent(), true);
        $password = $userPasswordEncoder->encodePassword($user , $data['password']);
        $user->setUsername($data['username'])
            ->setPassword($password)
            ->setRoles($data['roles']);
        $entityManager->persist($user);
        $entityManager->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user), Response::HTTP_OK);
    }
    /*
     * Fonction qui retourne tous les films ou retourne des films en fonction de filtres
     */

    /**
     * @Route("/film", name="film")
     * @param FilmsRepository $filmsRepository
     * @param Request $request
     * @return JsonResponse
     */
    public function film(FilmsRepository $filmsRepository, Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(Films::class)->getFieldNames();
        foreach ($metadata as $value){
            if ($request->query->get($value)){
                $filter[$value] = $request->query->get($value);
            }
        }
        return JsonResponse::fromJsonString($this->serializeJson($filmsRepository->findBy($filter)), Response::HTTP_OK);
    }

}
