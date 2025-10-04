<?php

namespace App\Controller\API\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\DriverManager;

class LoginAuth extends AbstractController {
    
    #[Route('/api/login-auth',name:"login-auth",methods:['POST'])]
    public function doGetUser(Request $req):JsonResponse{
        
        try {
            // Get password from user Input
            $userInput = json_decode($req->getContent(), true);
            $username = $userInput['username'];
            $password = $userInput['password'];

            // Create database connection
            $connectionParams = [
                'host' => '127.0.0.1',
                'port' => 3307,
                'dbname' => 'ToothalieDb',
                'user' => 'clint',
                'password' => 'clinT',
                'driver' => 'pdo_mysql'
            ];
            
            $connection = DriverManager::getConnection($connectionParams);
            
            // Query patient table with proper column names
            $patient = $connection->fetchAssociative(
                "SELECT patient_id, username, first_name, last_name, role, password FROM patient WHERE username = ?",
                [$username]
            );
            
            if (!$patient) {
                return new JsonResponse([
                    'status' => "username or password incorrect"
                ], 401);
            }
            
            // Verify password (in production, use password_hash/password_verify)
            if (!password_verify($password, $patient['password'])) {
                return new JsonResponse([
                    'status' => "username or password incorrect"
                ], 401);
            }
            
            // Return user data (without password)
            return new JsonResponse([
                'status' => 'ok',
                'userID' => $patient['patient_id'],
                'username' => $patient['username'],
                'firstName' => $patient['first_name'],
                'lastName' => $patient['last_name'],
                'role' => $patient['role']
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
