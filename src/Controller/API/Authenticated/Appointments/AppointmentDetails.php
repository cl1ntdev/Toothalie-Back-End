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

            // 1. Get appointment
            $appointment = $connection->fetchAssociative(
                "SELECT * FROM appointments WHERE patient_id = ?",
                [$userID]
            );

            if (!$appointment) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'No appointment found for this user'
                ], 404);
            }

            // 2. Get dentist details
            $dentist = $connection->fetchAssociative(
                "SELECT * FROM dentist WHERE dentistID = ?",
                [$appointment['dentist_id']]
            );

            // 3. Get all schedules for that dentist
            $schedules = $connection->fetchAllAssociative(
                "SELECT * FROM schedule WHERE dentistID = ? ORDER BY day_of_week, time_slot",
                [$appointment['dentist_id']]
            );

            return new JsonResponse([
                'status' => 'ok',
                'appointment' => $appointment,
                'dentist' => $dentist,
                'schedules' => $schedules
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
