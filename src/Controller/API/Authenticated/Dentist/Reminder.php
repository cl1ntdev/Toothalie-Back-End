<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class Reminder extends AbstractController
{
    #[Route('/api/save-reminder', name: "save-reminder", methods: ['POST'])]
    public function saveReminder(Request $req, Connection $connection): JsonResponse
    {
        date_default_timezone_set('Asia/Manila');
    
        try {
            $data = json_decode($req->getContent(), true);
    
            $payload = $data['payload'] ?? null;          // array of days
            $appointmentID = $data['appointmentID'] ?? null; // string/int
    
            if (!$payload || !$appointmentID) {
                return new JsonResponse([
                    "status" => "error",
                    "message" => "Missing payload or appointmentID"
                ], 400);
            }
    
            // Check if reminder already exists for appointmentID
            $existing = $connection->fetchAssociative(
                "SELECT * FROM reminder WHERE appointment_id = ?",
                [$appointmentID]
            );
    
            if ($existing) {
                // update
                $connection->update('reminder', [
                    'information' => json_encode($payload)
                ], ['appointment_id' => $appointmentID]);
            } else {
                // insert
                $connection->insert('reminder', [
                    'appointment_id' => $appointmentID,
                    'information'    => json_encode($payload)
                ]);
            }
    
            return new JsonResponse([
                "status" => "success",
                "message" => "Reminder saved successfully",
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

}
