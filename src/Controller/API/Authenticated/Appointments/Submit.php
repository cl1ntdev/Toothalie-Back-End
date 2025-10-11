<?php

namespace App\Controller\API\Authenticated\Appointments;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class Submit extends AbstractController
{
    #[Route('/api/add-appointment', name: "add-appointment", methods: ['POST'])]
    public function addAppointment(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);

            // Validate request data
            if (!$data || !isset($data['patientID'], $data['dentistID'], $data['day'], $data['time'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing required fields: patientID, dentistID, day, time'
                ], 400);
            }

            $patientID = $data['patientID'];
            $dentistID = $data['dentistID'];
            $day = $data['day'];
            $time = $data['time'];
            
            
            // set emergency value to be passed
            $emergency=$data['emergency'];
            $setEmergency = $emergency == True ? 1 : 0;
            
            // set family value to be passed 
            $familyBooking=$data['familyBooking'];
            $setAppointmentType = $familyBooking == True ? 2 : 1;    // 1 is Normal and 2 is Family 
            
            $setDate = $data['date'];
            $status = "Pending"; // defualt valUE
            $message = $data['message'];
            
            
            // Fetch scheduleID
            $schedule = $connection->fetchAssociative(
                "SELECT scheduleID FROM schedule WHERE dentistID = ? AND day_of_week = ? AND time_slot = ?",
                [$dentistID, $day, $time]
            );

            if ($schedule === false || !isset($schedule['scheduleID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'No schedule found for the selected dentist, day, and time'
                ], 400);
            }

            $scheduleID = $schedule['scheduleID'];

            // Insert appointment
            $connection->insert('appointment', [
                'patient_id' => $patientID,
                'dentist_id' => $dentistID,
                'schedule_id' => $scheduleID,
                'emergency' => $setEmergency,
                'appointment_type_id'=> $setAppointmentType,
                'user_set_date'=> $setDate,
                'status'=>$status,
                'message'=>$message
                 
            ]);

            return new JsonResponse([
                'status' => 'ok',
                'message' => 'Appointment booked successfully'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to book appointment: ' . $e->getMessage()
            ], 500);
        }
    }
}
