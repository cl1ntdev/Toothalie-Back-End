<?php

namespace App\Controller\API;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetUsers extends AbstractController {
    
    #[Route('/api/getuser',name:"get_user")]
    public function doGetUser(){
        echo "working route";
    }
}
