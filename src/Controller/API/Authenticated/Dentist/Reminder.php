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

    #[Route('/api/get-reminder', name:'get-reminder', methods:['POST'])]
    public function getReminder(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;
    
            if (!$appointmentID) {
                return new JsonResponse([
                    "status" => "error",
                    "message" => "Missing appointmentID"
                ], 400);
            }
    
            $reminder = $connection->fetchAssociative(
                "SELECT information FROM reminder WHERE appointment_id = ?",
                [$appointmentID]
            );
    
            if (!$reminder) {
                return new JsonResponse([
                    "status" => "success",
                    "message" => "No reminder found",
                    "data" => null
                ]);
            }
    
            return new JsonResponse([
                "status" => "success",
                "message" => "Reminder fetched successfully",
                "data" => json_decode($reminder['information'], true)
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }
    #[Route('/api/update-reminder', name:'update-reminder', methods:['POST'])]
    public function updateReminder(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;
            $payload = $data['payload'] ?? null; // new schedule array
    
            if (!$appointmentID || !$payload) {
                return new JsonResponse([
                    "status" => "error",
                    "message" => "Missing appointmentID or payload"
                ], 400);
            }
    
            $existing = $connection->fetchAssociative(
                "SELECT * FROM reminder WHERE appointment_id = ?",
                [$appointmentID]
            );
    
            if ($existing) {
                // Update existing
                $connection->update('reminder', [
                    'information' => json_encode($payload)
                ], ['appointment_id' => $appointmentID]);
    
                return new JsonResponse([
                    "status" => "success",
                    "message" => "Reminder updated successfully"
                ]);
            } else {
                // If no reminder exists, insert new
                $connection->insert('reminder', [
                    'appointment_id' => $appointmentID,
                    'information' => json_encode($payload)
                ]);
    
                return new JsonResponse([
                    "status" => "success",
                    "message" => "Reminder created successfully"
                ]);
            }
    
        } catch (\Exception $e) {
            return new JsonResponse([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

}
