<?php

namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ArrayParameterType;

final class GetHistory extends AbstractController
{
    #[Route('/api/get-history', name: 'app_get_history', methods: ['POST'])]
    public function index(Request $request, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $userID = $data['userID'] ?? null;
            $role = $data['role'];

            $queryBase = "";
            
            $queryBase = match ($role) {
                'DENTIST' => "SELECT appointment_id FROM appointment WHERE dentist_id = ?",
                'PATIENT' => "SELECT appointment_id FROM appointment WHERE patient_id = ?",
                default   => null,
            };

            if (!$userID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing required parameter: userID',
                ], 400);
            }
            
            // patient_id and dentist_id
            
            //Get all appointment IDs for this patient
            $ids = $connection->fetchAllAssociative(
                $queryBase,
                // "SELECT appointment_id FROM appointment WHERE patient_id = ? AND deleted_on IS NULL",
                [$userID]
            );
            $appointmentIDs = array_column($ids, 'appointment_id');

            if (empty($appointmentIDs)) {
                return new JsonResponse([
                    'status' => 'ok',
                    'message' => 'No appointments found for this patient',
                    'data' => []
                ]);
            }

            //  Fetch all logs related to those appointments
            $logs = $connection->fetchAllAssociative(
                "SELECT 
                    al.*, 
                    a.patient_id, 
                    a.dentist_id, 
                    a.status, 
                    a.user_set_date
                FROM appointment_log al
                JOIN appointment a ON a.appointment_id = al.appointment_id
                WHERE al.appointment_id IN (?) and al.actor_type = (?)
                ORDER BY al.logged_at DESC",
                [$appointmentIDs,$role],
                [ArrayParameterType::INTEGER]
                );

            return new JsonResponse([
            // for testing
                // 'query' => $queryBase,
                // 'userID' => $userID,
                // 'role' => $role,
                // 'appointmentID' => $appointmentIDs,
                
                //
                'status' => 'ok',
                'count' => count($logs),
                'data' => $logs
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to fetch history: ' . $e->getMessage()
            ], 500);
        }
    }
}
