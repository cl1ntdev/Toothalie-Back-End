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
        
        $userInput = json_decode($req->getContent(), true);
        $username = $userInput['username'];
        $password = $userInput['password'];
        return new JsonResponse([
               'status' => 'ok',
               'username' => $username,
               // never send password back in real life — just showing structure
        ]);    }
}
