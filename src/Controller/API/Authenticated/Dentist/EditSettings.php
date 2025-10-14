<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class EditSettings extends AbstractController
{
    #[Route('/api/update-dentist-settings', name: 'update-dentist-settings', methods: ['POST'])]
    public function Edit(Request $req, Connection $conn): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        $scheduleGroups = $data['schedules'] ?? [];

        if (empty($scheduleGroups)) {
            return new JsonResponse(['message' => 'No schedules provided'], 400);
        }

        $dentistID = $scheduleGroups[0]['dentistID'] ?? null;
        if (!$dentistID) {
            return new JsonResponse(['message' => 'Missing dentistID'], 400);
        }

        $existing = $conn->fetchAllAssociative(
            "SELECT scheduleID FROM schedule WHERE dentistID = ?",
            [$dentistID]
        );
        $existingIDs = array_column($existing, 'scheduleID');

        $newIDs = [];

        foreach ($scheduleGroups as $group) {
            $day = $group['day_of_week'] ?? null;
            $timeSlots = $group['time_slots'] ?? [];

            if (!$day || empty($timeSlots)) continue;

            foreach ($timeSlots as $slot) {
                $scheduleID = $slot['scheduleID'] ?? null;
                $time = $slot['time'] ?? null;
                if (!$time) continue;

                if ($scheduleID && in_array($scheduleID, $existingIDs)) {
                    // Update existing schedule
                    $conn->update('schedule', [
                        'day_of_week' => $day,
                        'time_slot' => $time,
                    ], ['scheduleID' => $scheduleID]);
                    $newIDs[] = $scheduleID;
                } else {
                    // Insert new schedule
                    $conn->insert('schedule', [
                        'day_of_week' => $day,
                        'time_slot' => $time,
                        'dentistID' => $dentistID
                    ]);
                    $newIDs[] = $conn->lastInsertId();
                }
            }
        }

        //delete removed schedules
        $toDelete = array_diff($existingIDs, $newIDs);
        
        if (!empty($toDelete)) {
            //convert values to integers (safety)
            $toDelete = array_map('intval', $toDelete);
        
            //build a dynamic placeholder list like (?, ?, ?)
            $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
        
            // delete query safely
            $conn->executeStatement(
                "DELETE FROM schedule WHERE scheduleID IN ($placeholders)",
                $toDelete
            );
        }


        return new JsonResponse([
        'message' => 'Dentist schedule updated successfully',
            'status' => 'ok',
            'updated' => $newIDs,
            'deleted' => array_values($toDelete),
        ]);
    }
}
