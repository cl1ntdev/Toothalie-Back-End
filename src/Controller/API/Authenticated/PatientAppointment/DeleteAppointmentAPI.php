<?php

namespace App\Controller\API\Authenticated\Appointments;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class DeleteAppointmentAPI extends AbstractController
{
    #[Route('/api/delete-appointment', name: "delete-appointment", methods: ['POST'])]
    public function deleteAppointment(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;

            if (!$appointmentID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing appointmentID'
                ], 400);
            }

            // Delete the appointment
            $affectedRows = $connection->executeStatement(
                "DELETE FROM appointment WHERE appointment_id = ?",
                [$appointmentID]
            );

            if ($affectedRows === 0) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'No appointment found with the given ID'
                ], 404);
            }

            return new JsonResponse([
                'status' => 'ok',
                'message' => 'Appointment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to delete appointment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
