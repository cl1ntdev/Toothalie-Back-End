<?php

namespace App\Controller\API\Authenticated\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use App\Service\ActivityLogger;

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
    public function deleteSchedule(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
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
    
            $connection->delete('schedule', ['scheduleID' => $scheduleID]);

            // Log deletion
            $logger->log(
                'SCHEDULE_DELETED',
                "Admin deleted schedule ID {$scheduleID} (Dentist ID: {$schedule['dentistID']}, Day: {$schedule['day_of_week']}, Time: {$schedule['time_slot']})"
            );
    
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
    public function updateSchedule(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
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
    
            $updateData = [
                'day_of_week' => $data['day_of_week'] ?? null,
                'time_slot'   => $data['time_slot'] ?? null,
                'dentistID'   => $data['dentistID'] ?? null,
            ];
    
            $updateData = array_filter($updateData, fn($v) => $v !== null);
    
            $connection->update('schedule', $updateData, ['scheduleID' => $scheduleID]);

            // Log update
            $logger->log(
                'SCHEDULE_UPDATED',
                "Admin updated schedule ID {$scheduleID} (Fields: " . implode(', ', array_keys($updateData)) . ")"
            );
    
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
    public function createSchedule(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            $required = ['day_of_week', 'time_slot', 'dentistID']; 
            foreach ($required as $field) {
                if (empty($data[$field]) && $data[$field] !== 0 && $data[$field] !== '0') {
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => "Missing required field: $field"
                    ], 400);
                }
            }
            
            $insertData = [
                'day_of_week' => $data['day_of_week'],
                'time_slot'   => $data['time_slot'],
                'dentistID'   => $data['dentistID'],
            ];
    
            $connection->insert('schedule', $insertData);
    
            $newScheduleId = $connection->lastInsertId();

            // Log creation
            $logger->log(
                'SCHEDULE_CREATED',
                "Admin created schedule ID {$newScheduleId} (Dentist ID: {$data['dentistID']}, Day: {$data['day_of_week']}, Time: {$data['time_slot']})"
            );
    
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
