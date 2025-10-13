<?php

namespace App\Controller\API\Authenticated\PatientAppointment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class UpdateAppointment extends AbstractController
{
    // >> >> >> << << << 
    // 
    // UPDATE APPOINTMENT VALUE BY THE PATIENT
    // 
    // >> >> >> << << << 
    #[Route('/api/update-appointment', name: "update-appointment", methods: ['POST'])]
    public function updateAppointment(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointmentID'] ?? null;
            $scheduleID = $data['scheduleID'] ?? null;
            $date = $data['date'];
            
            $emergency = !empty($data['isEmergency']) ? 1 : 0;
            $appointment_type_id = !empty($data['isFamilyBooking']) ? 2 : 1;
            $message = $data['message'];
            
        
            
            
            if (!$appointmentID || !$scheduleID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'appointmentID and scheduleID are required'
                ], 400);
            }

            $appointment = $connection->fetchAssociative(
                "SELECT * FROM appointment WHERE appointment_id = ?",
                [$appointmentID]
            );

            if (!$appointment) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Appointment not found'
                ], 404);
            }

            $connection->update(
            'appointment',
               [
                   'schedule_id' => $scheduleID,
                   'user_set_date' => $date,
                   'emergency' => $emergency,
                   'appointment_type_id'=> $appointment_type_id,
                   'message'=> $message
               ],
                ['appointment_id'=>$appointmentID]
            );

            return new JsonResponse([
                'status' => 'ok',
                'message' => 'Appointment updated successfully',
                'appointmentID' => $appointmentID,
                'newScheduleID' => $scheduleID
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
