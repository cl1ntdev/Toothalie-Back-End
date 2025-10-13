<?php

namespace App\Controller\API\Authenticated\PatientAppointment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class AppointmentDetails extends AbstractController
{
    // >> >> >> << << << 
    // 
    // Returns all appointments made by the user
    // 
    // >> >> >> << << << 
    #[Route('/api/get-appointment', name: "get-appointment", methods: ['POST'])]
    public function getAppointment(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $userID = $data['userID'] ?? null;

            if (!$userID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing userID'
                ], 400);
            }

            $appointments = $connection->fetchAllAssociative(
                "SELECT * FROM appointment WHERE patient_id = ? ORDER BY appointment_id DESC",
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
                // Dentist info
                $dentist = $connection->fetchAssociative(
                    "SELECT * FROM dentist WHERE dentistID = ?",
                    [$appointment['dentist_id']]
                );

                $schedules = $connection->fetchAllAssociative(
                    "SELECT * FROM schedule WHERE dentistID = ? ORDER BY day_of_week, time_slot",
                    [$appointment['dentist_id']]
                );

                $results[] = [
                    'appointment' => $appointment,
                    'dentist' => $dentist,
                    'schedules' => $schedules
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
