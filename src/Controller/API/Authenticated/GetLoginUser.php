<?php
//  THIS WILL RETURN A LOGIN USER 
namespace App\Controller\API\Authenticated;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetLoginUser extends AbstractController {
    
    #[Route('/api/get-login-user',name:"get-login-user",methods:['POST'])]
    public function doGetUser(Request $req):JsonResponse{

        $userReq = json_decode($req->getContent(), true);
        $reqIDBase = $userReq['id'];
        $getUserLoginQuery = "select * from users where id = ?";
        
        $userID = 123;
        $usernameTest = "clint123";
        $firstName = "Clint";
        $lastName = "Estrellanes";
        $role = "Patient";
        $passwordTest = "admin";
        
        if($reqIDBase != $userID){
            return new JsonResponse([
                'status' => "no id found"
            ]);
        };
        
        return new JsonResponse([
           'status'=>'ok',
           'userID' => $userID,
           'username' => $firstName,
           'lastname' => $lastName,
           'role' => $role,
        ]);}
}
