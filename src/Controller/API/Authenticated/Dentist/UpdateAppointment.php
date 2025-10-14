<?php

namespace App\Controller\API\Authenticated\Dentist;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class UpdateAppointment extends AbstractController
{
    #[Route('/api/edit-appointment-dentist', name: "edit-appointment-dentist", methods: ['POST'])]
    public function getAppointment(Request $req, Connection $connection): JsonResponse
    {
        // >> >> >> << << << 
        // 
        // Returns boolean for checking status if it is successful or not
        // 
        // >> >> >> << << << 
        try {
            $data = json_decode($req->getContent(), true);
            $appointmentID = $data['appointment_id'] ?? null;
            $status = $data['status'];
            
            
            if (!$appointmentID) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing appointmentid'
                ], 400);
            }

            $update = $connection->update(
                "appointment",
                ['status' => $status],
                ['appointment_id' => $appointmentID]
            );
            

          
            return new JsonResponse([
                'status' => 'ok',
                'update_status' => $update 
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
