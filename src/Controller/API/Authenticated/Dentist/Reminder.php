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
    
                $payload = $data['payload'] ?? null;
                $appointmentID = $data['appointmentID'] ?? null;
    
                if (!$payload || !$appointmentID) {
                    return new JsonResponse([
                        "status" => "error",
                        "message" => "Missing payload or appointmentID"
                    ], 400);
                }
    
                // Check if reminder already exists
                $existing = $connection->fetchAssociative(
                    "SELECT * FROM reminder WHERE appointment_id = ?",
                    [$appointmentID]
                );
    
                if ($existing) {
                    // Update
                    $connection->update('reminder', [
                        'information' => json_encode($payload)
                    ], ['appointment_id' => $appointmentID]);
    
                    $action = "Reminder Updated";
                    $message = "Reminder updated for appointmentID {$appointmentID}";
                    $snapshot = json_encode($existing); // previous reminder
                } else {
                    // Insert
                    $connection->insert('reminder', [
                        'appointment_id' => $appointmentID,
                        'information'    => json_encode($payload)
                    ]);
    
                    $action = "Reminder Created";
                    $message = "Reminder created for appointmentID {$appointmentID}";
                    $snapshot = json_encode($payload);
                }
    
                // Insert log
                $connection->insert('appointment_log', [
                    'appointment_id' => $appointmentID,
                    'logged_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'actor_type' => 'DENTIST',
                    'action' => $action,
                    'message' => $message,
                    'snapshot' => $snapshot,
                ]);
    
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
               $payload = $data['payload'] ?? null;
   
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
                   $connection->update('reminder', [
                       'information' => json_encode($payload)
                   ], ['appointment_id' => $appointmentID]);
   
                   $action = "Reminder Updated";
                   $message = "Reminder updated for appointmentID {$appointmentID}";
                   $snapshot = json_encode($existing);
   
                   $responseMessage = "Reminder updated successfully";
               } else {
                   $connection->insert('reminder', [
                       'appointment_id' => $appointmentID,
                       'information' => json_encode($payload)
                   ]);
   
                   $action = "Reminder Created";
                   $message = "Reminder created for appointmentID {$appointmentID}";
                   $snapshot = json_encode($payload);
   
                   $responseMessage = "Reminder created successfully";
               }
   
               // Log
               $connection->insert('appointment_log', [
                   'appointment_id' => $appointmentID,
                   'logged_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                   'actor_type' => 'DENTIST',
                   'action' => $action,
                   'message' => $message,
                   'snapshot' => $snapshot,
               ]);
   
               return new JsonResponse([
                   "status" => "success",
                   "message" => $responseMessage
               ]);
   
           } catch (\Exception $e) {
               return new JsonResponse([
                   "status" => "error",
                   "message" => $e->getMessage()
               ], 500);
           }
       }
}
