<?php
//  THIS WILL RETURN A LOGIN USER 
namespace App\Controller\API\Authenticated;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;
class GetUserInfo extends AbstractController {
   
    #[Route('/api/get-user-info',name:'get-user-info',methods:'POST')]
    public function getUserInfo(Request $req, Connection $conn):JsonResponse{
        $data = json_decode($req->getContent(),true);
        $id = $data['id'];

        $userInfo = $conn->fetchAssociative(
            "select patient_id, username, first_name, last_name, role, created_at, contact_no, email from patient where patient_id = ?",
            [$id]
        );
        if(!$userInfo){
            return new JsonResponse([
                'status'=>"no user found"
            ]);
        }
        return new JsonResponse([
            "status" => "ok",
            "user" => $userInfo
        ]);
        
    }
}
