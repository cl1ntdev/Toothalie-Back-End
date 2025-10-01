<?php
// THIS WILL RETURN ALL DENTISTS
namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GetDentists extends AbstractController {
    
    #[Route('/api/dentists', name: "get-dentists", methods: ['GET'])]
    public function getDentists(): JsonResponse
    {
        // Sample data (later you can fetch from DB)
        $dentists = [
            [
                "name" => "Dr. Jane Doe",
                "email" => "jane.doe@toothalie.com",
                "experience" => "8 years",
                "specialty" => "Orthodontist"
            ],
            [
                "name" => "Dr. John Smith",
                "email" => "john.smith@toothalie.com",
                "experience" => "12 years",
                "specialty" => "Endodontist"
            ],
            [
                "name" => "Dr. Maria Garcia",
                "email" => "maria.garcia@toothalie.com",
                "experience" => "6 years",
                "specialty" => "Pediatric Dentist"
            ]
        ];

        return new JsonResponse([
            "status" => "ok",
            "dentists" => $dentists
        ]);
    }
}
