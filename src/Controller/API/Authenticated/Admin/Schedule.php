<?php

namespace App\Controller\API\Authenticated\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class Schedule extends AbstractController
{
    #[Route('/api/admin/get-schedules', name: "get-schedules", methods: ['GET'])]
    public function getSchedules(Connection $connection): JsonResponse
    {
        try {
            $schedules = $connection->fetchAllAssociative(
                'SELECT s.scheduleID, s.day_of_week, s.time_slot, s.dentistID,
                        u.first_name as dentist_first_name, u.last_name as dentist_last_name
                 FROM schedule s
                 JOIN user u ON s.dentistID = u.id'
            );
            
            return new JsonResponse([
                'schedules' => $schedules
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/get-schedule', name: "get-schedule", methods: ['POST'])]
    public function getOneSchedule(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $scheduleID = $data['scheduleID'];
            
            $schedule = $connection->fetchAssociative(
                'SELECT s.scheduleID, s.day_of_week, s.time_slot, s.dentistID,
                        u.first_name as dentist_first_name, u.last_name as dentist_last_name
                 FROM schedule s
                 JOIN user u ON s.dentistID = u.id
                 WHERE s.scheduleID = ?',
                [$scheduleID]
            );
            
            return new JsonResponse([
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/delete-schedule', name: "delete-schedule", methods: ['POST'])]
    public function deleteSchedule(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!isset($data['scheduleID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing schedule ID'
                ], 400);
            }
    
            $scheduleID = $data['scheduleID'];
    
            // Check schedule exists
            $schedule = $connection->fetchAssociative(
                "SELECT * FROM schedule WHERE scheduleID = ?",
                [$scheduleID]
            );
    
            if (!$schedule) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Schedule not found'
                ], 404);
            }
    
            // Hard delete
            $connection->delete('schedule', ['scheduleID' => $scheduleID]);
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Schedule deleted permanently',
                'schedule_id' => $scheduleID
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    
    #[Route('/api/admin/update-schedule', name: "update-schedule", methods: ['POST'])]
    public function updateSchedule(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!$data || !isset($data['scheduleID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing schedule ID',
                ], 400);
            }
    
            $scheduleID = $data['scheduleID'];
    
            // Prepare update data
            $updateData = [
                'day_of_week'  => $data['day_of_week'] ?? null,
                'time_slot'      => $data['time_slot'] ?? null,
                'dentistID'   => $data['dentistID'] ?? null,
            ];
    
            // Remove NULL values so fields remain unchanged
            $updateData = array_filter($updateData, fn($v) => $v !== null);
    
            // Update database
            $connection->update('schedule', $updateData, ['scheduleID' => $scheduleID]);
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Schedule updated successfully',
                'updated' => $updateData
            ], 200);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/create-schedule', name: "create-schedule", methods: ['POST'])]
    public function createSchedule(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            // Validate required fields
            $required = ['day_of_week', 'time_slot', 'dentistID']; 
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
                'day_of_week'  => $data['day_of_week'],
                'time_slot'      => $data['time_slot'],
                'dentistID'   => $data['dentistID'],
            ];
    
            // Insert into database
            $connection->insert('schedule', $insertData);
    
            // Get new ID
            $newScheduleId = $connection->lastInsertId();
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Schedule created successfully',
                'schedule_id' => $newScheduleId,
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
