<?php

namespace App\Controller\API\Authenticated\Appointments;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;

class SpecifiedAppointment extends AbstractController
{
    #[Route('/api/specified-appointment', name: "specified-appointment", methods: ['POST'])]
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
            $appointmentID = $data['appointmentID'] ?? null;

            if (!$appointmentID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'appointmentID is required'
                ], 400);
            }

            // 1. Get the appointment
            $appointment = $connection->fetchAssociative(
                "SELECT * FROM appointments WHERE appointment_id = ?",
                [$appointmentID]
            );

            if (!$appointment) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Appointment not found'
                ], 404);
            }

            // 2. Get dentist info
            $dentist = $connection->fetchAssociative(
                "SELECT * FROM dentist WHERE dentistID = ?",
                [$appointment['dentist_id']]
            );

            // 3. Get schedules for that dentist
            $schedules = $connection->fetchAllAssociative(
                "SELECT scheduleID, day_of_week, time_slot 
                 FROM schedule 
                 WHERE dentistID = ? 
                 ORDER BY day_of_week, time_slot",
                [$appointment['dentist_id']]
            );

            // Group schedules by day
            $groupedSchedules = [];
            foreach ($schedules as $s) {
                $groupedSchedules[$s['day_of_week']][] = [
                    'scheduleID' => $s['scheduleID'],
                    'time_slot'  => $s['time_slot']
                ];
            }

            // 4. Get the specific schedule used in this appointment
            $selectedSchedule = $connection->fetchAssociative(
                "SELECT scheduleID, day_of_week, time_slot 
                 FROM schedule 
                 WHERE scheduleID = ?",
                [$appointment['schedule_id']]
            );

            // 5. Return unified response
            return new JsonResponse([
                'status' => 'ok',
                'appointment' => $appointment,
                'dentist' => $dentist,
                'schedules' => $groupedSchedules,
                'scheduleDetails' => $selectedSchedule
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
