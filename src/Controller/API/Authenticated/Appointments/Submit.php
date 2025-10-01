<?php

namespace App\Controller\API\Authenticated\Appointments;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\DriverManager;

class Submit extends AbstractController {

    #[Route('/api/add-appointment', name: "add-appointment", methods: ['POST'])]
    public function addAppointment(Request $req): JsonResponse {
        try {
            // DB connection
            $connectionParams = [
                'host' => '127.0.0.1',
                'port' => 3307,
                'dbname' => 'ToothalieDb',
                'user' => 'clint',
                'password' => 'clinT',
                'driver' => 'pdo_mysql'
            ];
            $connection = DriverManager::getConnection($connectionParams);
            
            $data = json_decode($req->getContent(), true);

            // if (!$data || !isset($data['patientID'], $data['dentistID'], $data['scheduleID'])) {
            //     return new JsonResponse([
            //         'status' => 'error',
            //         'message' => 'Missing required fields'
            //     ], 400);
            // }

            // $patientID = $data['patientID'];
            // $dentistID = $data['dentistID'];
            // $scheduleID = $data['scheduleID']; // must be ID, not string

            $patientID = 1;
            $dentistID = 1;
            $scheduleID = 1;

            
            // Insert into appointments
            $connection->insert('appointments', [
                'patient_id' => $patientID,
                'dentist_id' => $dentistID,
                'schedule_id' => $scheduleID
                // appointment_date will auto-fill with CURRENT_TIMESTAMP
            ]);

            return new JsonResponse([
                'status' => 'ok',
                'message' => 'Appointment booked successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'patient_id' => $patientID,
                'dentist_id' => $dentistID,
                'schedule_id' => $scheduleID
            ], 500);
        }
    }
}
