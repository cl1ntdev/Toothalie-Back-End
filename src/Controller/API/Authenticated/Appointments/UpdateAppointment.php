<?php

namespace App\Controller\API\Authenticated\Appointments;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class UpdateAppointment extends AbstractController
{
    #[Route('/api/update-appointment', name: "update-appointment", methods: ['POST'])]
    public function updateAppointment(Request $req, Connection $connection): JsonResponse
    {
        try {
            // Parse request body
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;
            $scheduleID = $data['scheduleID'] ?? null;

            if (!$appointmentID || !$scheduleID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'appointmentID and scheduleID are required'
                ], 400);
            }

            // Check if appointment exists
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

            // Update schedule_id in appointment
            $connection->update(
                'appointment',
                ['schedule_id' => $scheduleID],
                ['appointment_id' => $appointmentID]
            );

            return new JsonResponse([
                'status' => 'ok',
                'message' => 'Appointment updated successfully',
                'appointmentID' => $appointmentID,
                'newScheduleID' => $scheduleID
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
