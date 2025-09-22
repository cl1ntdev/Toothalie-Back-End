<?php

namespace App\Controller\API;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegUser extends AbstractController {
    
    #[Route('/api/reg-check-user',name:"reg-auth",methods:['POST'])]
    public function doGetUser(Request $req):JsonResponse{
        
        $userInput = json_decode($req->getContent(),true);
        $usernameInput = $userInput["username"];
        
        // sample data retrieve from database \\
        $username = "clint123";
        $firstName = "Clint";
        $lastName = "Estrellanes";
        $role = "Patient";
        $passwordTest = "1234";
        // sample data retrieve from database \\
        
        if($usernameInput == $username){
            return new JsonResponse([
                'status'=> "username already exist please choose another one"
            ]);
        };
        
        return new JsonResponse([
               'status' => 'ok',
               'username' => $firstName,
               'lastname' => $lastName,
               'password' => $role,
               // never send password back in real life — just showing structure
        ]);    
    }
}
