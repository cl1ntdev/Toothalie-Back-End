<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class AppointmentDetails extends AbstractController
{
    #[Route('/api/get-appointment-dentist', name: "get-appointment-dentist", methods: ['GET'])]
    public function getAppointment(Request $req, Connection $connection): JsonResponse
    {
        try {

            $user = $this->getUser();
            $dentistID = $user->getId();
            if (!$dentistID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing dentistID'
                ], 400);
            }

            //Fetch all appointments assigned to this dentist
            $appointments = $connection->fetchAllAssociative(
                "SELECT * FROM appointment 
                 WHERE dentist_id = ? AND deleted_on IS NULL 
                 ORDER BY appointment_id DESC",
                [$dentistID]
            );

            if (!$appointments) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'No appointments found for this dentist'
                ], 404);
            }

            $results = [];
            foreach ($appointments as $appointment) {

                //Fetch the patient details
                $patient = $connection->fetchAssociative(
                    "SELECT id, username, first_name, last_name, email 
                     FROM user 
                     WHERE id = ?",
                    [$appointment['patient_id']]
                );

                //Fetch patient roles
                $patientRoles = $connection->fetchAllAssociative(
                    "SELECT r.role_name 
                     FROM user_role ur
                     INNER JOIN role r ON ur.role_id = r.id
                     WHERE ur.user_id = ?",
                    [$appointment['patient_id']]
                );

                //Fetch schedule details
                $schedule = $connection->fetchAssociative(
                    "SELECT scheduleID, day_of_week, time_slot 
                     FROM schedule 
                     WHERE scheduleID = ?",
                    [$appointment['schedule_id']]
                );

                //Fetch dentist info + roles
                $dentist = $connection->fetchAssociative(
                    "SELECT id, username, first_name, last_name, email 
                     FROM user 
                     WHERE id = ?",
                    [$appointment['dentist_id']]
                );

                $dentistRoles = $connection->fetchAllAssociative(
                    "SELECT r.role_name 
                     FROM user_role ur
                     INNER JOIN role r ON ur.role_id = r.id
                     WHERE ur.user_id = ?",
                    [$appointment['dentist_id']]
                );

                $results[] = [
                    'appointment' => $appointment,
                    'patient' => [
                        ...$patient,
                        'roles' => array_column($patientRoles, 'role_name')
                    ],
                    'dentist' => [
                        ...$dentist,
                        'roles' => array_column($dentistRoles, 'role_name')
                    ],
                    'schedule' => $schedule
                ];
            }

            return new JsonResponse([
                'status' => 'ok',
                'appointments' => $results
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
