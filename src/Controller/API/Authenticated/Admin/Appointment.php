<?php

namespace App\Controller\API\Authenticated\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class Appointment extends AbstractController
{
    #[
        Route(
            "/api/admin/appointments",
            name: "admin_appointments_list",
            methods: ["GET"],
        ),
    ]
    public function getAppointments(
        Request $req,
        Connection $connection,
    ): JsonResponse {
        try {
            $appointments = $connection->fetchAllAssociative('
                SELECT
                    a.appointment_id,
                    a.appointment_date,
                    a.status,
                    a.message,
                    a.emergency,
                    a.user_set_date,
                    p.id AS patient_id,
                    CONCAT(p.first_name, " ", p.last_name) AS patient_name,
                    p.username AS patient_username,
                    d.id AS dentist_id,
                    CONCAT(d.first_name, " ", d.last_name) AS dentist_name,
                    d.username AS dentist_username,
                    atype.id AS appointment_type_id,
                    atype.appointment_name AS appointment_type,
                    s.id AS service_id,
                    s.name AS service_name,
                    st.id AS service_type_id,
                    st.name AS service_type_name,
                    sch.scheduleID AS schedule_id,
                    sch.day_of_week,
                    sch.time_slot
                FROM appointment a
                LEFT JOIN user p ON a.patient_id = p.id
                LEFT JOIN user d ON a.dentist_id = d.id
                LEFT JOIN appointment_type atype ON a.appointment_type_id = atype.id
                LEFT JOIN service s ON a.service_id = s.id
                LEFT JOIN service_type st ON s.service_type_id = st.id
                LEFT JOIN schedule sch ON a.schedule_id = sch.scheduleID
                ORDER BY a.appointment_date DESC;
            ');

            $rawSchedules = $connection->fetchAllAssociative('
                SELECT scheduleID, day_of_week, time_slot, dentistID
                FROM schedule
                ORDER BY dentistID, day_of_week, time_slot
            ');

            $schedules = [];
            foreach ($rawSchedules as $row) {
                $dentist = $row["dentistID"];
                $schedules[$dentist][] = $row;
            }

            $rawServices = $connection->fetchAllAssociative('
                SELECT ds.user_id AS dentist_id, s.id AS service_id, s.name AS service_name,
                       st.id AS service_type_id, st.name AS service_type_name
                FROM dentist_service ds
                LEFT JOIN service s ON ds.service_id = s.id
                LEFT JOIN service_type st ON s.service_type_id = st.id
                ORDER BY ds.user_id
            ');

            $services = [];
            foreach ($rawServices as $row) {
                $dentist = $row["dentist_id"];
                $services[$dentist][] = $row;
            }

            return new JsonResponse([
                "appointments" => $appointments,
                "schedules" => $schedules,
                "services" => $services,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(
                ["status" => "error", "message" => $e->getMessage()],
                500,
            );
        }
    }

    #[
        Route(
            "/api/admin/appointments/{id}",
            name: "admin_appointment_get",
            methods: ["GET"],
        ),
    ]
    public function getOneAppointment(
        int $id,
        Connection $connection,
    ): JsonResponse {
        try {
            $appointment = $connection->fetchAssociative(
                "SELECT * FROM appointment WHERE appointment_id = ?",
                [$id],
            );
            if (!$appointment) {
                return new JsonResponse(
                    ["status" => "error", "message" => "Appointment not found"],
                    404,
                );
            }
            return new JsonResponse(["appointment" => $appointment]);
        } catch (\Exception $e) {
            return new JsonResponse(
                ["status" => "error", "message" => $e->getMessage()],
                500,
            );
        }
    }

    #[
        Route(
            "/api/admin/appointments",
            name: "admin_appointment_create",
            methods: ["POST"],
        ),
    ]
    public function createAppointment(
        Request $req,
        Connection $connection,
    ): JsonResponse {
        try {
            $data = json_decode($req->getContent(), true);
            $required = ["patient_id", "dentist_id", "schedule_id"];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === "") {
                    return new JsonResponse(
                        [
                            "status" => "error",
                            "message" => "Missing required field: $field",
                        ],
                        400,
                    );
                }
            }

            $insertData = [
                "patient_id" => (int) $data["patient_id"],
                "dentist_id" => (int) $data["dentist_id"],
                "schedule_id" => (int) $data["schedule_id"],
                "emergency" => isset($data["emergency"])
                    ? (int) $data["emergency"]
                    : 0,
                "user_set_date" => $data["user_set_date"] ?? null,
                "status" => $data["status"] ?? "pending",
                "message" => $data["message"] ?? null,
                "service_id" => isset($data["service_id"])
                    ? (int) $data["service_id"]
                    : null,
                "appointment_date" => new \DateTime()->format("Y-m-d H:i:s"),
            ];

            if (!empty($insertData["service_id"])) {
                $service = $connection->fetchAssociative(
                    "SELECT service_type_id FROM service WHERE id = ?",
                    [$insertData["service_id"]],
                );
                $insertData["appointment_type_id"] =
                    $service["service_type_id"] ?? null;
            }

            $connection->insert(
                "appointment",
                array_filter($insertData, fn($v) => $v !== null),
            );
            $newId = $connection->lastInsertId();

            return new JsonResponse([
                "status" => "success",
                "message" => "Appointment created",
                "appointment_id" => $newId,
                "data" => $insertData,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(
                ["status" => "error", "message" => $e->getMessage()],
                500,
            );
        }
    }

    #[
        Route(
            "/api/admin/appointments/{id}",
            name: "admin_appointment_update",
            methods: ["PUT", "PATCH"],
        ),
    ]
    public function updateAppointment(
        string $id,
        Request $req,
        Connection $connection,
    ): JsonResponse {
        try {
                $data = json_decode($req->getContent(), true);
        
                if (!isset($data['appointmentID'])) {
                    return new JsonResponse([
                        "status" => "error",
                        "message" => "Missing appointment ID",
                    ], 400);
                }
        
                $appointmentID = (int)$data['appointmentID'];
        
                $updateData = [
                    "patient_id" => isset($data["patient_id"]) ? (int)$data["patient_id"] : null,
                    "dentist_id" => isset($data["dentist_id"]) ? (int)$data["dentist_id"] : null,
                    "schedule_id" => isset($data["schedule_id"]) ? (int)$data["schedule_id"] : null,
                    "emergency" => isset($data["emergency"]) ? (int)$data["emergency"] : null,
                    "user_set_date" => $data["user_set_date"] ?? null,
                    "status" => $data["status"] ?? null,
                    "message" => $data["message"] ?? null,
                    "service_id" => isset($data["service_id"]) ? (int)$data["service_id"] : null,
                ];
        
                // Remove nulls so only provided fields are updated
                $updateData = array_filter($updateData, fn($v) => $v !== null);
        
                if (empty($updateData)) {
                    return new JsonResponse([
                        "status" => "error",
                        "message" => "No valid fields to update",
                    ], 400);
                }
        
                $connection->update("appointment", $updateData, ["appointment_id" => $appointmentID]);
        
                return new JsonResponse([
                    "status" => "success",
                    "message" => "Appointment updated",
                    "updated" => $updateData,
                ]);
            } catch (\Exception $e) {
                return new JsonResponse([
                    "status" => "error",
                    "message" => $e->getMessage(),
                ], 500);
            }
    }

    #[
        Route(
            "/api/admin/appointments/{id}",
            name: "admin_appointment_delete",
            methods: ["DELETE"],
        ),
    ]
    public function deleteAppointment(
        int $id,
        Connection $connection,
    ): JsonResponse {
        try {
            $appointment = $connection->fetchAssociative(
                "SELECT * FROM appointment WHERE appointment_id = ?",
                [$id],
            );
            if (!$appointment) {
                return new JsonResponse(
                    ["status" => "error", "message" => "Appointment not found"],
                    404,
                );
            }

            $connection->delete("appointment", ["appointment_id" => $id]);
            return new JsonResponse([
                "status" => "success",
                "message" => "Appointment deleted",
                "appointment_id" => $id,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(
                ["status" => "error", "message" => $e->getMessage()],
                500,
            );
        }
    }
}
