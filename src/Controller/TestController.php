<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;

class TestController extends AbstractController
{
    #[Route('/api/test-db', name: 'test_db', methods: ['GET'])]
    public function testDB(Connection $connection): JsonResponse
    {
        try {
            // Get a simple array of table names
            $tables = $connection->fetchFirstColumn('SHOW TABLES');

            return new JsonResponse([
                'status' => 'ok',
                'tables' => $tables
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
