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
            //Fetch all users with the DENTIST role via user_role join
            $dentists = $connection->fetchAllAssociative(
                "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.roles
                 FROM user u
                 JOIN user_role ur ON ur.user_id = u.id
                 JOIN role r ON r.id = ur.role_id
                 WHERE r.role_name = 'DENTIST'"
            );

            //Attach schedules for each dentist
            foreach ($dentists as &$dentist) {
                $schedules = $connection->fetchAllAssociative(
                    "SELECT day_of_week, time_slot FROM schedule WHERE dentistID = ? ORDER BY day_of_week, time_slot",
                    [$dentist['id']]
                );
                $dentistServices = $connection->fetchAllAssociative(
                    "SELECT s.id as serviceID, s.name as serviceName, se.id as serviceTypeID, se.name as serviceTypeName from service s 
                    join dentist_service d on d.service_id = s.id 
                    join service_type se on se.id = s.service_type_id where d.user_id = ? ",
                    [$dentist['id']]
                );

                // Group schedules by day
                $grouped = [];
                foreach ($schedules as $s) {
                    $grouped[$s['day_of_week']][] = $s['time_slot'];
                }

                $dentist['schedule'] = $grouped;
                $dentist['services'] = $dentistServices;
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

    #[Route('/api/dentist-info', name:'dentist-info', methods:['GET'])]
    public function getLoggedInDentistInfo(Connection $con): JsonResponse
    {

        $user = $this->getUser();
        $dentistID = $user->getId();
        
        if (!$dentistID) {
            return new JsonResponse([
                'status' => 'error',
                'message'=> 'No dentist ID provided'
            ], 400);
        }

        try {
            $dentistInfo = $con->fetchAssociative(
                "SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.roles
                 FROM user u
                 JOIN user_role ur ON ur.user_id = u.id
                 JOIN role r ON r.id = ur.role_id
                 WHERE u.id = ? AND r.role_name = 'DENTIST'",
                [$dentistID]
            );

            if (!$dentistInfo) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Dentist not found'
                ], 404);
            }

            $schedule = $con->fetchAllAssociative(
                "SELECT scheduleID, day_of_week, time_slot FROM schedule WHERE dentistID = ? ORDER BY day_of_week, time_slot",
                [$dentistID]
            );

            return new JsonResponse([
                'status' => 'ok',
                'dentist' => $dentistInfo,
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
