<?php

namespace App\API;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetUser extends AbstractController {
    
    #[Route('/api/getuser',name:"get_user")]
    public function doGetUser(){
        echo "working route";
    }
}
