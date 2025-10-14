<?php
namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class GetDentists extends AbstractController {
    
    #[Route('/api/dentists', name: "get-dentists", methods: ['GET'])]
    public function getDentists(Connection $connection): JsonResponse
    {
        try {
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
        } catch (\Exception $e) {
            return new JsonResponse([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }
    
    #[Route('/api/dentist-info',name:'dentist-info',methods:['POST'])]
    public function getLoggedInDentistInfo(Request $req,Connection $con):JsonResponse{
        $data = json_decode($req->getContent(),true);
        $dentistID = $data['dentistID'];
        if(!$dentistID){
            return new JsonResponse([
                'status' => 'error',
                'message'=> 'no dentist id found'
            ]);
        }
        
        try{
            $dentistInfo = $con->fetchAssociative(
            "Select * from dentist where dentistID = ?",
            [$dentistID]
            );
            $schedule = $con->fetchAllAssociative(
            "Select * from schedule where dentistID = ?",
            [$dentistID]
            );
            return new JsonResponse([
                'status' => 'ok',
                'dentist' => $dentistInfo,
                'schedule' => $schedule
            ]);
        }catch(e){
            return new JsonResponse([
                'status' => 'error',
                'message' => e
            ]);
        }
        
    }
}
