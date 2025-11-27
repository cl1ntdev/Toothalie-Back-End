<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class EditSettings extends AbstractController
{
    #[
        Route(
            "/api/update-dentist-settings",
            name: "update-dentist-settings",
            methods: ["POST"],
        ),
    ]
    public function Edit(Request $req, Connection $conn): JsonResponse
    {
        $conn->beginTransaction();
        try {
            $user = $this->getUser();
            $dentistID = $user->getId();

            $data = json_decode($req->getContent(), true);
            $schedules = $data['schedules'];

            if ($schedules === null) {
                return new JsonResponse(
                    ["status" => "error", "message" => "Invalid JSON payload"],
                    400,
                );
            }

            if (!$dentistID && !empty($schedules)) {
                $dentistID = $schedules[0]["dentistID"] ?? null;
            }

            if (!$dentistID) {
                return new JsonResponse(
                    ["status" => "error", "message" => "Missing dentistID"],
                    400,
                );
            }

            $dentist = $conn->fetchAssociative(
                "SELECT id, username, first_name, last_name, email
                 FROM user WHERE id = ?",
                [$dentistID],
            );

            if (!$dentist) {
                return new JsonResponse(
                    ["status" => "error", "message" => "User not found"],
                    404,
                );
            }

            $roles = $conn->fetchAllAssociative(
                "SELECT r.role_name
                 FROM user_role ur
                 INNER JOIN role r ON ur.role_id = r.id
                 WHERE ur.user_id = ?",
                [$dentistID],
            );

            $roleNames = array_map(
                "strtolower",
                array_column($roles, "role_name"),
            );
            if (!in_array("dentist", $roleNames)) {
                return new JsonResponse(
                    [
                        "status" => "error",
                        "message" => "User is not authorized as a dentist",
                    ],
                    403,
                );
            }

            // Fetch existing schedules for this dentist
            $existing = $conn->fetchAllAssociative(
                "SELECT scheduleID FROM schedule WHERE dentistID = ?",
                [$dentistID],
            );
            $existingIDs = array_column($existing, "scheduleID");
            $processedIDs = [];

            // Process incoming schedules
            foreach ($schedules as $schedule) {
                $scheduleID = $schedule["scheduleID"] ?? null;
                $day = $schedule["day_of_week"] ?? null;
                $time = trim($schedule["time_slot"] ?? "");

                if (!$day || !$time) {
                    continue; // Skip entries with missing data
                }

                if ($scheduleID && in_array($scheduleID, $existingIDs)) {
                    // Update existing schedule
                    $conn->update(
                        "schedule",
                        [
                            "day_of_week" => $day,
                            "time_slot" => $time,
                        ],
                        ["scheduleID" => $scheduleID],
                    );
                    $processedIDs[] = (int) $scheduleID;
                } elseif (is_null($scheduleID)) {
                    // Insert new schedule
                    $conn->insert("schedule", [
                        "day_of_week" => $day,
                        "time_slot" => $time,
                        "dentistID" => $dentistID,
                    ]);
                    $processedIDs[] = (int) $conn->lastInsertId();
                }
            }

            $processedIDs = array_unique($processedIDs);

            // Delete schedules that were not in the payload
            $toDelete = array_diff($existingIDs, $processedIDs);
            $deletedIDs = [];
            $notDeletedIDs = [];

            if (!empty($toDelete)) {
                $placeholders = implode(
                    ",",
                    array_fill(0, count($toDelete), "?"),
                );
                $deleteParams = array_map("intval", $toDelete);

                // Check if any schedules to be deleted are referenced in appointments
                $referencedIDs =
                    $conn->fetchFirstColumn(
                        "SELECT DISTINCT schedule_id FROM appointment WHERE schedule_id IN ($placeholders)",
                        $deleteParams,
                    ) ?:
                    [];

                $safeToDelete = array_diff($toDelete, $referencedIDs);
                if (!empty($safeToDelete)) {
                    $placeholdersDelete = implode(
                        ",",
                        array_fill(0, count($safeToDelete), "?"),
                    );
                    $conn->executeStatement(
                        "DELETE FROM schedule WHERE scheduleID IN ($placeholdersDelete)",
                        array_map("intval", $safeToDelete),
                    );
                    $deletedIDs = $safeToDelete;
                }

                $notDeletedIDs = $referencedIDs;
            }

            $conn->commit();

            return new JsonResponse([
                "status" => "ok",
                "message" => "Dentist schedule updated successfully",
                "dentist" => array_merge($dentist, ["roles" => $roleNames]),
                "processed" => array_values($processedIDs),
                "deleted" => array_values($deletedIDs),
                "not_deleted_due_to_appointments" => array_values(
                    $notDeletedIDs,
                ),
            ]);
        } catch (\Exception $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            return new JsonResponse(
                [
                    "status" => "error",
                    "message" => $e->getMessage(),
                ],
                500,
            );
        }
    }
    
    #[Route('/api/edit-services', name: '/api/edit-services', methods: ['POST'])]
    public function editServices(Request $request, Connection $connection): JsonResponse
    {
        $user = $this->getUser();
        $userID = $user->getId();
        $data = json_decode($request->getContent(), true);
    
        if (!$data || !isset($data['payload'])) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }
    
        $payload = $data['payload'];
    
        try {
            $connection->beginTransaction();
    
            // Fetch CURRENT dentist services from DB
            $existing = $connection->fetchFirstColumn(
                "SELECT service_id FROM dentist_service WHERE user_id = ?",
                [$userID]
            );
    
            $existing = array_map('intval', $existing);
    
            // Extract NEW service IDs from payload
            $newServiceIds = array_map(fn ($item) => (int)$item['service_id'], $payload);
    
            // Remove duplicates just in case
            $newServiceIds = array_unique($newServiceIds);
    
            // Determine INSERTS & DELETES
            $toInsert = array_diff($newServiceIds, $existing);
            $toDelete = array_diff($existing, $newServiceIds);
    
            //  Perform INSERTS
            foreach ($toInsert as $serviceID) {
                $connection->insert('dentist_service', [
                    'user_id' => $userID,
                    'service_id' => $serviceID
                ]);
            }
    
            //  Perform DELETES
            foreach ($toDelete as $serviceID) {
                $connection->delete('dentist_service', [
                    'user_id' => $userID,
                    'service_id' => $serviceID
                ]);
            }
    
            // Commit changes
            $connection->commit();
    
            return new JsonResponse([
                'status' => 'success',
                'added' => array_values($toInsert),
                'removed' => array_values($toDelete),
                'finalServices' => $newServiceIds
            ]);
    
        } catch (\Throwable $e) {
            $connection->rollBack();
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
