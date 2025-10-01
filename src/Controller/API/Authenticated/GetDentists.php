<?php
namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;

class GetDentists extends AbstractController {
    
    #[Route('/api/dentists', name: "get-dentists", methods: ['GET'])]
    public function getDentists(): JsonResponse
    {
        // Create database connection using direct parameters
        $connectionParams = [
            'host' => '127.0.0.1',
            'port' => 3307,
            'dbname' => 'ToothalieDb',
            'user' => 'clint',
            'password' => 'clinT',
            'driver' => 'pdo_mysql'
        ];
        
        $connection = DriverManager::getConnection($connectionParams);
        
        // 1. Fetch all dentists
        $dentists = $connection->fetchAllAssociative("SELECT * FROM dentist");

        // 2. Attach schedules for each dentist
        foreach ($dentists as &$dentist) {
            $schedules = $connection->fetchAllAssociative(
                "SELECT day_of_week, time_slot FROM schedule WHERE dentistID = ?",
                [$dentist['dentistID']]
            );

            // Group times by day
            $grouped = [];
            foreach ($schedules as $s) {
                $grouped[$s['day_of_week']][] = $s['time_slot'];
            }

            $dentist['schedule'] = $grouped;
        }

        return new JsonResponse([
            "status" => "ok",
            "dentists" => $dentists
        ]);
    }
}
