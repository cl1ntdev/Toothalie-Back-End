<?php

namespace App\Controller\API\Authenticated\Patient;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class AppointmentDetails extends AbstractController
{
    #[Route('/api/get-appointment', name: "get_appointment", methods: ['GET'])]
    public function getAppointment(Connection $connection): JsonResponse
    {
        try {

            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $userID = $user->getId();

            $appointments = $connection->fetchAllAssociative(
                "SELECT * FROM appointment 
                 WHERE patient_id = ? AND deleted_on IS NULL 
                 ORDER BY appointment_id DESC",
                [$userID]
            );

            if (!$appointments) {
                return new JsonResponse([
                    'status' => 'ok',
                    'appointments' => []
                ]);
            }

            $results = [];
            foreach ($appointments as $appointment) {

                $dentist = $connection->fetchAssociative(
                    "SELECT id, username, first_name, last_name, email, roles 
                     FROM user WHERE id = ?",
                    [$appointment['dentist_id']]
                );

                $schedules = $connection->fetchAllAssociative(
                    "SELECT * FROM schedule 
                     WHERE dentistID = ? 
                     ORDER BY day_of_week, time_slot",
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
