<?php

namespace App\Controller;

use App\Entity\Region;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/regions/api", name="api_add_region_api", methods={"GET"})
     */
    public function addRegionByApi(SerializerInterface $serializer)
    {
        $regionJson=file_get_contents("https://geo.api.gouv.fr/regions");
        //Method 1
        //Decode JSON vers Tableau
        // $regionTab=$serializer->decode($regionJson,"json");
        // //Dernormalisation Tableau vers Objet ou Tableau Objet
        // $regionObject=$serializer->denormalize($regionTab, 'App\Entity\Region[]');

        //Method 2 
        $regionObject = $serializer->deserialize($regionJson,'App\Entity\Region[]','json');
        $entityManager = $this->getDoctrine()->getManager();
        foreach($regionObject as $region){
            $entityManager->persist($region);
        }
        $entityManager->flush();
        
        return new JsonResponse("succes",201,[],true);

    }

    /**
     * @Route("/api/regions", name="api_show_region_api", methods={"GET"})
     */
    public function showRegion(SerializerInterface $serializer,RegionRepository $repo)
    {
        $regionsObject=$repo->findAll();
        $regionsJson =$serializer->serialize($regionsObject,"json",
        [
            "groups"=>["region:read_all"]
        ]
        );
        return new JsonResponse($regionsJson,Response::HTTP_OK,[],true);
    }
    /**
    * @Route("/api/regions", name="api_add_region_api",methods={"POST"})
    */
    public function addRegion(Request $request,ValidatorInterface $validator,SerializerInterface $serializer)
    {
        $region = $serializer->deserialize($request->getContent(), Region::class,'json');
        $errors = $validator->validate($region);
        if (count($errors) > 0) {
            $errorsString =$serializer->serialize($errors,"json");
            return new JsonResponse( $errorsString ,Response::HTTP_BAD_REQUEST,[],true);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($region);
        $entityManager->flush();
        return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
    }
}
