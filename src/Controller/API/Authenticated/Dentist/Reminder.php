<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use App\Service\ActivityLogger;

class Reminder extends AbstractController
{
    #[Route('/api/save-reminder', name: "save-reminder", methods: ['POST'])]
    public function saveReminder(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
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

                $actionKey = 'REMINDER_UPDATED';
                $message = "Reminder updated for appointmentID {$appointmentID}";
                $snapshotData = $existing; // Pass array, logger handles serialization
            } else {
                // Insert
                $connection->insert('reminder', [
                    'appointment_id' => $appointmentID,
                    'information'    => json_encode($payload)
                ]);

                $actionKey = 'REMINDER_CREATED';
                $message = "Reminder created for appointmentID {$appointmentID}";
                $snapshotData = $payload;
            }

            // Log the action via Service
            $logger->log(
                $actionKey,
                $message,
                null,
                [
                    'actor_type' => 'DENTIST',
                    'appointment_id' => $appointmentID,
                    'snapshot' => $snapshotData
                ]
            );

            return new JsonResponse([
                "status" => "success",
                "message" => "Reminder saved successfully",
            ]);

        } catch (\Exception $e) {
            // Log error
            $logger->log(
                'ERROR',
                "Failed to save reminder: " . $e->getMessage(),
                null,
                ['actor_type' => 'DENTIST']
            );

            return new JsonResponse([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/get-reminder', name:'get-reminder', methods:['POST'])]
    public function getReminder(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
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
            // Log error
            $logger->log(
                'ERROR',
                "Failed to fetch reminder: " . $e->getMessage(),
                null,
                ['actor_type' => 'DENTIST']
            );

            return new JsonResponse([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/update-reminder', name:'update-reminder', methods:['POST'])]
    public function updateReminder(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
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

                $actionKey = 'REMINDER_UPDATED';
                $message = "Reminder updated for appointmentID {$appointmentID}";
                $snapshotData = $existing;
                $responseMessage = "Reminder updated successfully";
            } else {
                $connection->insert('reminder', [
                    'appointment_id' => $appointmentID,
                    'information' => json_encode($payload)
                ]);

                $actionKey = 'REMINDER_CREATED';
                $message = "Reminder created for appointmentID {$appointmentID}";
                $snapshotData = $payload;
                $responseMessage = "Reminder created successfully";
            }

            // Log the action via Service
            $logger->log(
                $actionKey,
                $message,
                null,
                [
                    'actor_type' => 'DENTIST',
                    'appointment_id' => $appointmentID,
                    'snapshot' => $snapshotData,
                ]
            );

            return new JsonResponse([
                "status" => "success",
                "message" => $responseMessage
            ]);

        } catch (\Exception $e) {
            // Log error
            $logger->log(
                'ERROR',
                "Failed to update reminder: " . $e->getMessage(),
                null,
                ['actor_type' => 'DENTIST']
            );

            return new JsonResponse([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }
}