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
            $role = strtoupper(trim($data['role'] ?? ''));

            if (!$userID || !$role) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing required parameters: userID or role',
                ], 400);
            }

            // Step 1: Get all appointment IDs for this user depending on their role
            $queryBase = match ($role) {
                'DENTIST' => 'SELECT appointment_id FROM appointment WHERE dentist_id = ? AND deleted_on IS NULL',
                'PATIENT' => 'SELECT appointment_id FROM appointment WHERE patient_id = ? AND deleted_on IS NULL',
                default => null,
            };

            if (!$queryBase) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid role provided',
                ], 400);
            }

            $appointmentIDs = $connection->fetchFirstColumn($queryBase, [$userID]);

            if (empty($appointmentIDs)) {
                return new JsonResponse([
                    'status' => 'ok',
                    'message' => 'No appointments found for this user',
                    'data' => []
                ]);
            }

            $logQuery = '
                SELECT 
                    al.*, 
                    a.patient_id, 
                    a.dentist_id, 
                    a.status, 
                    a.user_set_date
                FROM appointment_log al
                JOIN appointment a ON a.appointment_id = al.appointment_id
                WHERE al.appointment_id IN (?)
            ';
            
            if ($role === 'DENTIST') {
                // Only logs made by the dentist themselves
                $logQuery .= ' AND al.actor_type = ? AND a.dentist_id = ? ORDER BY al.logged_at DESC';
                
                $logs = $connection->fetchAllAssociative(
                    $logQuery,
                    [$appointmentIDs, 'DENTIST', $userID], // first is array for IN(?)
                    [ArrayParameterType::INTEGER] // only the array
                );
            
            } else { // PATIENT
                // All logs for the patient's appointments
                $logQuery .= ' AND a.patient_id = ? ORDER BY al.logged_at DESC';
                
                $logs = $connection->fetchAllAssociative(
                    $logQuery,
                    [$appointmentIDs, $userID],
                    [ArrayParameterType::INTEGER]
                );
            }



            return new JsonResponse([
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
