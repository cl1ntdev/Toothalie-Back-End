<?php

namespace App\Controller\API\Authenticated\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class Reminder extends AbstractController
{
    #[Route('/api/admin/get-reminders', name: "get-reminders", methods: ['GET'])]
    public function getReminders(Connection $connection): JsonResponse
    {
        try {
            $reminders = $connection->fetchAllAssociative(
                'SELECT * from reminder'
            );
            
            return new JsonResponse([
                'reminders' => $reminders
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/get-reminder', name: "get-reminder", methods: ['POST'])]
    public function getOneReminder(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $reminderID = $data['reminderID'];
            
            $reminder = $connection->fetchAssociative(
                'SELECT * from reminder where id = ?',
                [$reminderID]
            );
            
            return new JsonResponse([
                'reminder' => $reminder
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/delete-reminder', name: "delete-reminder", methods: ['POST'])]
    public function deleteReminder(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!isset($data['reminderID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing reminder ID'
                ], 400);
            }
    
            $reminderID = $data['reminderID'];
    
            // Check reminder exists
            $reminder = $connection->fetchAssociative(
                "SELECT * FROM reminder WHERE id = ?",
                [$reminderID]
            );
    
            if (!$reminder) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Reminder not found'
                ], 404);
            }
    
            // Hard delete
            $connection->delete('reminder', ['id' => $reminderID]);
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Reminder deleted permanently',
                'reminder_id' => $reminderID
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    
    #[Route('/api/admin/update-reminder', name: "update-reminder", methods: ['POST'])]
    public function updateReminder(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!$data || !isset($data['reminderID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing reminder ID',
                ], 400);
            }
    
            $reminderID = $data['reminderID'];
    
            // Prepare update data
            $updateData = [
                'information'  => isset($data['information']) ? json_encode($data['information']) : null,
                'appointment_id'      => $data['appointment_id'] ?? null,
                'viewed'   => $data['viewed'] ?? null,
            ];
    
            // Remove NULL values so fields remain unchanged
            $updateData = array_filter($updateData, fn($v) => $v !== null);
    
            // Update database
            $connection->update('reminder', $updateData, ['id' => $reminderID]);
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Reminder updated successfully',
                'updated' => $updateData
            ], 200);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/create-reminder', name: "create-reminder", methods: ['POST'])]
    public function createReminder(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            // Validate required fields
            $required = ['information']; 
            foreach ($required as $field) {
                if (empty($data[$field]) && $data[$field] !== 0 && $data[$field] !== '0') {
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => "Missing required field: $field"
                    ], 400);
                }
            }
            
            // Prepare insert data
            $insertData = [
                'information'  => json_encode($data['information']),
                'appointment_id'      => $data['appointment_id'] ?? null,
                'viewed'   => $data['viewed'] ?? 0, // Default to 0 if not provided
            ];
    
            // Insert into database
            $connection->insert('reminder', $insertData);
    
            // Get new ID
            $newReminderId = $connection->lastInsertId();
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Reminder created successfully',
                'reminder_id' => $newReminderId,
                'data' => $insertData
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
