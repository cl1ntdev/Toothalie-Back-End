<?php

namespace App\Controller\API;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;

class GetUsers extends AbstractController {
    
    #[Route('/api/getuser',name:"get_user", methods:['GET'])]
    public function doGetUser(): JsonResponse {
        try {
            // Create database connection
            $connectionParams = [
                'host' => '127.0.0.1',
                'port' => 3307,
                'dbname' => 'ToothalieDb',
                'user' => 'clint',
                'password' => 'clinT',
                'driver' => 'pdo_mysql'
            ];
            
            $connection = DriverManager::getConnection($connectionParams);
            
            // Fetch all patients
            $patients = $connection->fetchAllAssociative(
                "SELECT patient_id, username, first_name, last_name, role, created_at FROM patient"
            );
            
            return new JsonResponse([
                'status' => 'ok',
                'users' => $patients
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to fetch users: ' . $e->getMessage()
            ], 500);
        }
    }
}
