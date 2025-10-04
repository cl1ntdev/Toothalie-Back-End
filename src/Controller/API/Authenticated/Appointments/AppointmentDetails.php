<?php

namespace App\Controller\API\Authenticated\Appointments;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;

class AppointmentDetails extends AbstractController
{
    #[Route('/api/get-appointment', name: "get-appointment", methods: ['POST'])]
    public function getAppointment(Request $req): JsonResponse
    {
        try {
            // DB connection
            $connectionParams = [
                'host' => '127.0.0.1',
                'port' => 3307,
                'dbname' => 'ToothalieDb',
                'user' => 'clint',
                'password' => 'clinT',
                'driver' => 'pdo_mysql'
            ];
            $connection = DriverManager::getConnection($connectionParams);

            // Parse request body
            $data = json_decode($req->getContent(), true);
            $userID = $data['userID'] ?? null;

            if (!$userID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing userID'
                ], 400);
            }

            // 1. Get ALL appointments for the user
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

            // 2. Enrich each appointment
            $results = [];
            foreach ($appointments as $appointment) {
                // Dentist info
                $dentist = $connection->fetchAssociative(
                    "SELECT * FROM dentist WHERE dentistID = ?",
                    [$appointment['dentist_id']]
                );

                // Dentist schedules
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
