<?php

namespace App\Controller\API\Authenticated\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use App\Service\ActivityLogger;

class DentistService extends AbstractController
{
    #[Route('/api/admin/get-dentist-services', name: "get-dentist-services", methods: ['GET'])]
    public function getDentistServices(Connection $connection): JsonResponse
    {
        try {
            $dentistServices = $connection->fetchAllAssociative(
                'SELECT ds.id, ds.user_id, ds.service_id, 
                        u.first_name as dentist_first_name, u.last_name as dentist_last_name,
                        s.name as service_name, st.name as service_type_name
                 FROM dentist_service ds
                 JOIN user u ON ds.user_id = u.id
                 JOIN service s ON ds.service_id = s.id
                 JOIN service_type st ON s.service_type_id = st.id'
            );
            
            return new JsonResponse([
                'dentist_services' => $dentistServices
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/get-dentist-service', name: "get-dentist-service", methods: ['POST'])]
    public function getOneDentistService(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $dentistServiceID = $data['dentistServiceID'];
            
            $dentistService = $connection->fetchAssociative(
                'SELECT ds.id, ds.user_id, ds.service_id, 
                        u.first_name as dentist_first_name, u.last_name as dentist_last_name,
                        s.name as service_name, st.name as service_type_name
                 FROM dentist_service ds
                 JOIN user u ON ds.user_id = u.id
                 JOIN service s ON ds.service_id = s.id
                 JOIN service_type st ON s.service_type_id = st.id
                 WHERE ds.id = ?',
                [$dentistServiceID]
            );
            
            return new JsonResponse([
                'dentist_service' => $dentistService
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/delete-dentist-service', name: "delete-dentist-service", methods: ['POST'])]
    public function deleteDentistService(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!isset($data['dentistServiceID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing dentist service ID'
                ], 400);
            }
    
            $dentistServiceID = $data['dentistServiceID'];
    
            $dentistService = $connection->fetchAssociative(
                "SELECT * FROM dentist_service WHERE id = ?",
                [$dentistServiceID]
            );
    
            if (!$dentistService) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Dentist service not found'
                ], 404);
            }
    
            $connection->delete('dentist_service', ['id' => $dentistServiceID]);

            // Log deletion
            $logger->log(
                'DENTIST_SERVICE_DELETED',
                "Admin deleted dentist service ID {$dentistServiceID} (User ID: {$dentistService['user_id']}, Service ID: {$dentistService['service_id']})"
            );
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Dentist service deleted permanently',
                'dentist_service_id' => $dentistServiceID
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/update-dentist-service', name: "update-dentist-service", methods: ['POST'])]
    public function updateDentistService(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!$data || !isset($data['dentistServiceID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing dentist service ID',
                ], 400);
            }
    
            $dentistServiceID = $data['dentistServiceID'];
    
            $updateData = [
                'user_id'    => $data['user_id'] ?? null,
                'service_id' => $data['service_id'] ?? null,
            ];
    
            $updateData = array_filter($updateData, fn($v) => $v !== null);
    
            $connection->update('dentist_service', $updateData, ['id' => $dentistServiceID]);

            // Log update
            $logger->log(
                'DENTIST_SERVICE_UPDATED',
                "Admin updated dentist service ID {$dentistServiceID} (Fields: " . implode(', ', array_keys($updateData)) . ")"
            );
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Dentist service updated successfully',
                'updated' => $updateData
            ], 200);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/admin/create-dentist-service', name: "create-dentist-service", methods: ['POST'])]
    public function createDentistService(Request $req, Connection $connection, ActivityLogger $logger): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            $required = ['user_id', 'service_id']; 
            foreach ($required as $field) {
                if (empty($data[$field]) && $data[$field] !== 0 && $data[$field] !== '0') {
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => "Missing required field: $field"
                    ], 400);
                }
            }
    
            $userId = $data['user_id'];
            $serviceId = $data['service_id'];
    
            $userExists = $connection->fetchOne('SELECT id FROM user WHERE id = ?', [$userId]);
            if (!$userExists) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => "Invalid user_id, user does not exist"
                ], 400);
            }
    
            $serviceExists = $connection->fetchOne('SELECT id FROM service WHERE id = ?', [$serviceId]);
            if (!$serviceExists) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => "Invalid service_id, service does not exist"
                ], 400);
            }
    
            $insertData = [
                'user_id' => $userId,
                'service_id' => $serviceId,
            ];
    
            $connection->insert('dentist_service', $insertData);
    
            $newDentistServiceId = $connection->lastInsertId();

            // Log creation
            $logger->log(
                'DENTIST_SERVICE_CREATED',
                "Admin created dentist service ID {$newDentistServiceId} (User ID: {$userId}, Service ID: {$serviceId})"
            );
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Dentist service created successfully',
                'dentist_service_id' => $newDentistServiceId,
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
