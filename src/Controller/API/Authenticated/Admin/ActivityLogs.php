<?php

namespace App\Controller\API\Authenticated\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class ActivityLogs extends AbstractController
{
    #[Route('/api/admin/activity-logs', name: "get-activity-logs", methods: ['GET'])]
    public function getActivityLogs(Request $req, Connection $connection): JsonResponse
    {
        try {
            $userId = $req->query->get('userId');
            $action = $req->query->get('action');
            $dateFrom = $req->query->get('dateFrom');
            $dateTo = $req->query->get('dateTo');
            $limit = (int)($req->query->get('limit') ?? 50);
            $offset = (int)($req->query->get('offset') ?? 0);

            // 1. Build the Base Logic for Filtering
            // We use a closure or a helper method to apply filters to any QueryBuilder
            $applyFilters = function (QueryBuilder $qb) use ($userId, $action, $dateFrom, $dateTo) {
                if ($userId) {
                    $qb->andWhere('a.user_id = :userId')
                       ->setParameter('userId', $userId);
                }
                if ($action) {
                    $qb->andWhere('a.action = :action')
                       ->setParameter('action', $action);
                }
                if ($dateFrom) {
                    $qb->andWhere('DATE(a.created_at) >= :dateFrom')
                       ->setParameter('dateFrom', $dateFrom);
                }
                if ($dateTo) {
                    $qb->andWhere('DATE(a.created_at) <= :dateTo')
                       ->setParameter('dateTo', $dateTo);
                }
            };

            // 2. Fetch the Data
            $qb = $connection->createQueryBuilder();
            $qb->select('*')
               ->from('activity_log', 'a')
               ->orderBy('a.created_at', 'DESC')
               ->setMaxResults($limit)
               ->setFirstResult($offset);
            
            $applyFilters($qb);
            
            $logs = $qb->fetchAllAssociative();

            // 3. Fetch the Total Count (for pagination)
            $countQb = $connection->createQueryBuilder();
            $countQb->select('COUNT(*)')
                    ->from('activity_log', 'a');
            
            $applyFilters($countQb);

            $total = $countQb->fetchOne();

            // 4. Get Filters Data (Unique Users and Actions)
            // Note: We cache these or fetch simply. 
            $actions = $connection->fetchFirstColumn(
                "SELECT DISTINCT action FROM activity_log ORDER BY action"
            );

            $users = $connection->fetchAllAssociative(
                "SELECT DISTINCT user_id, username FROM activity_log WHERE user_id IS NOT NULL ORDER BY username"
            );

            return new JsonResponse([
                'status' => 'success',
                'logs' => $logs,
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'filters' => [
                    'actions' => $actions,
                    'users' => $users
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}