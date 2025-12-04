<?php

namespace App\Controller\API\Authenticated\Patient;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class DeleteAppointmentAPI extends AbstractController
{
    // >> >> >> << << << 
    // 
    // DELETE AN APPOINTMENT from patient
    // 
    // >> >> >> << << << 
    #[Route('/api/delete-appointment', name: "delete-appointment", methods: ['POST'])]
    public function deleteAppointment(Request $req, Connection $connection): JsonResponse
    {
        date_default_timezone_set('Asia/Manila');
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;

            if (!$appointmentID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing appointmentID'
                ], 400);
            }

            $affectedRows = $connection->executeStatement(
                "UPDATE appointment SET deleted_on = CURRENT_TIMESTAMP WHERE appointment_id = ?",
                [$appointmentID]
            );

            if ($affectedRows === 0) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'No appointment found with the given ID'
                ], 404);
            }
                      $appointment = $connection->fetchAssociative(
                          "SELECT * FROM appointment WHERE appointment_id = ?",
                          [$appointmentID]
                      );
          
                      $connection->insert('appointment_log', [
                          'appointment_id' => $appointmentID,
                          'actor_type' => 'PATIENT',
                          'action' => 'delete',
                          'message' => 'Deleted an appointment request.',
                          'snapshot' => json_encode($appointment ?: []),
                          'logged_at' => (new \DateTime())->format('Y-m-d H:i:s')
                      ]);
                      
                      
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
