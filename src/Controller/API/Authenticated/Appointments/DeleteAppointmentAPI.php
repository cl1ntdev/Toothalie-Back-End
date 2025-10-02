<?php

namespace App\Controller\API\Authenticated\Appointments;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;

class DeleteAppointmentAPI extends AbstractController
{
    #[Route('/api/delete-appointment', name: "delete-appointment", methods: ['POST'])]
    public function deleteAppointment(Request $req): JsonResponse
    {
        try {
            $connectionParams = [
                'host' => '127.0.0.1',
                'port' => 3307,
                'dbname' => 'ToothalieDb',
                'user' => 'clint',
                'password' => 'clinT',
                'driver' => 'pdo_mysql'
            ];
            $connection = DriverManager::getConnection($connectionParams);

            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;

            if (!$appointmentID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing appointmentID'
                ], 400);
            }

            // Delete the appointment
            $connection->executeStatement(
                "DELETE FROM appointments WHERE appointment_id = ?",
                [$appointmentID]
            );

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