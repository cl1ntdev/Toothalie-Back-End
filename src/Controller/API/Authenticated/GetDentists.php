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
            [
                "name" => "Dr. Jane Doe",
                "email" => "jane.doe@toothalie.com",
                "experience" => "8 years",
                "specialty" => "Orthodontist",
                "schedule" => [
                    "Monday" => ["09:00AM", "11:00AM", "01:00PM"],
                    "Wednesday" => ["10:00AM", "02:00PM"],
                    "Friday" => ["09:30AM", "01:30PM", "03:00PM"]
                ]
            ],
            [
                "name" => "Dr. John Smith",
                "email" => "john.smith@toothalie.com",
                "experience" => "12 years",
                "specialty" => "Endodontist",
                "schedule" => [
                    "Tuesday" => ["09:00AM", "11:30AM", "03:30PM"],
                    "Thursday" => ["10:00AM", "12:00PM", "04:00PM"],
                    "Saturday" => ["08:00AM", "10:00AM"]
                ]
            ],
            [
                "name" => "Dr. Maria Garcia",
                "email" => "maria.garcia@toothalie.com",
                "experience" => "6 years",
                "specialty" => "Pediatric Dentist",
                "schedule" => [
                    "Monday" => ["10:00AM", "01:00PM", "03:00PM"],
                    "Thursday" => ["09:30AM", "11:30AM", "01:30PM"],
                    "Friday" => ["08:00AM", "12:00PM"]
                ]
            ]
        ]
        ];

        return new JsonResponse([
            "status" => "ok",
            "dentists" => $dentists
        ]);
    }
}
