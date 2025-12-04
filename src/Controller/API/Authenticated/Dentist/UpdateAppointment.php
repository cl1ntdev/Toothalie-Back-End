<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class UpdateAppointment extends AbstractController
{
    #[Route('/api/edit-appointment-dentist', name: "edit-appointment-dentist", methods: ['POST'])]
    public function getAppointment(Request $req, Connection $connection): JsonResponse
    {
        date_default_timezone_set('Asia/Manila');
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointment_id'] ?? null;
            $status = $data['status'] ?? null;

            if (!$appointmentID || !$status) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing required fields: appointment_id or status'
                ], 400);
            }

            $appointment = $connection->fetchAssociative(
                'SELECT * FROM appointment WHERE appointment_id = ?',
                [$appointmentID]
            );

            if (!$appointment) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Appointment not found',
                ], 404);
            }

            $update = $connection->update(
                'appointment',
                ['status' => $status],
                ['appointment_id' => $appointmentID]
            );

            $connection->insert('appointment_log', [
                'appointment_id' => $appointmentID,
                'logged_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'actor_type' => 'DENTIST',
                'action' => 'Status Updated',
                'message' => "Status changed from '{$appointment['status']}' to '{$status}'",
                'snapshot' => json_encode($appointment),
            ]);

            return new JsonResponse([
                'status' => 'ok',
                'update_status' => $update,
                'message' => 'Appointment updated and logged successfully'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
