<?php

namespace App\Controller\API;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginAuth extends AbstractController {
    
    #[Route('/api/login-auth',name:"login-auth",methods:['POST'])]
    public function doGetUser(Request $req):JsonResponse{
        
        // Get password from user Input
        $userInput = json_decode($req->getContent(), true);
        $username = $userInput['username'];
        $password = $userInput['password'];

        $query = "Select * where username = ? and password = ?";
        
        // sample data retrieve from database \\
        $usernameTest = "clint123";
        $firstName = "Clint";
        $lastName = "Estrellanes";
        $role = "Patient";
        $passwordTest = "admin";
        // sample data retrieve from database \\
        
        if($username != $usernameTest || $password != $passwordTest){
            return new JsonResponse([
                'status'=>"username or password incorrect from symfony"
            ]);
        }
        
        // sample response from db
        return new JsonResponse([
               'status' => 'ok',
               'username' => $firstName,
               'lastname' => $lastName,
               'role' => $role,
               // never send password back in real life — just showing structure
        ]);    }
}
