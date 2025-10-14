<?php

namespace App\Controller\API\Authenticated\Patient;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class SpecifiedAppointment extends AbstractController
{
    // >> >> >> << << << 
    // 
    // Returns only one (1) Appointment of the user, used for editing
    // 
    // >> >> >> << << << 
    #[Route('/api/specified-appointment', name: "specified-appointment", methods: ['POST'])]
    public function getAppointment(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;

            if (!$appointmentID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'appointmentID is required'
                ], 400);
            }

            $appointment = $connection->fetchAssociative(
                "SELECT * FROM appointment WHERE appointment_id = ?",
                [$appointmentID]
            );

            if (!$appointment) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Appointment not found'
                ], 404);
            }

            $dentist = $connection->fetchAssociative(
                "SELECT * FROM dentist WHERE dentistID = ?",
                [$appointment['dentist_id']]
            );

            $schedules = $connection->fetchAllAssociative(
                "SELECT scheduleID, day_of_week, time_slot 
                 FROM schedule 
                 WHERE dentistID = ? 
                 ORDER BY day_of_week, time_slot",
                [$appointment['dentist_id']]
            );

            $groupedSchedules = [];
            foreach ($schedules as $s) {
                $groupedSchedules[$s['day_of_week']][] = [
                    'scheduleID' => $s['scheduleID'],
                    'time_slot'  => $s['time_slot']
                ];
            }

            $selectedSchedule = $connection->fetchAssociative(
                "SELECT scheduleID, day_of_week, time_slot 
                 FROM schedule 
                 WHERE scheduleID = ?",
                [$appointment['schedule_id']]
            );

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
