<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class AppointmentDetails extends AbstractController
{
    #[Route('/api/get-appointment-dentist', name: "get-appointment-dentist", methods: ['POST'])]
    public function getAppointment(Request $req, Connection $connection): JsonResponse
    {
        // >> >> >> << << << 
        // 
        // Returns Appointments assigned to Dentist
        // 
        // >> >> >> << << << 
        
        try {
            $data = json_decode($req->getContent(), true);
            $userID = $data['dentistID'] ?? null;

            if (!$userID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing userID'
                ], 400);
            }

            $appointments = $connection->fetchAllAssociative(
                "SELECT * FROM appointment WHERE dentist_id = ? ORDER BY appointment_id DESC",
                [$userID]
            );

            if (!$appointments) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'No appointments found for this user'
                ], 404);
            }

            $results = [];
            foreach ($appointments as $appointment) {
                $dentist = $connection->fetchAssociative(
                    "SELECT * FROM patient WHERE patient_id = ?",
                    [$appointment['patient_id']]
                );

                $schedule = $connection->fetchAllAssociative(
                    "SELECT * FROM schedule WHERE scheduleID = ? ORDER BY day_of_week, time_slot",
                    [$appointment['schedule_id']]
                );

                $results[] = [
                    'appointment' => $appointment,
                    'patients' => $dentist,
                ];
            }

            return new JsonResponse([
                'status' => 'ok',
                'appointments' => $results
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
