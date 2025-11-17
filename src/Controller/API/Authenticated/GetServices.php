<?php

namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ArrayParameterType;

final class GetServices extends AbstractController {
    #[Route('/api/get-services', methods:['GET'], name:'get-services')]
    public function getService(Connection $connection){

        $data = $connection->fetchAllAssociative(
            "Select s.id as service_id ,s.name as service_name, s.service_type_id, 
            se.id as serviceTypeId, se.name as serviceTypeName from service s
            join service_type se on s.service_type_id = se.id
            "
        );

        return new JsonResponse([
            'data' => $data
        ]);
        
    }
    
    #[Route('/api/get-dentist-service', methods:['POST'], name:'get-dentist-service')]
    public function getDentistServices(Request $request, Connection $connection){
        $data = json_decode($request->getContent(), true);
        $userID = $data['userID'];

        $dentistService = $connection->fetchAllAssociative(
            'SELECT * from dentist_service where user_id = ?',
            [$userID]
        );

        return new JsonResponse([
            'dentistServices' => $dentistService
        ]);
        
    }
    
}