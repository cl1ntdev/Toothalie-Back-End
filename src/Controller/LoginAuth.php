<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LoginAuth extends AbstractController {
    
    #[Route('/api/login-auth',name:"login-auth",methods:['POST'])]
    public function doGetUser(Request $req):Request{
        
        $userInput = json_decode($req->getContent(), true);
        $username = $userInput['username'];
        $password = $userInput['password'];

        echo $username;
        echo $password;
        
        return new Response('working route');
    }
}
