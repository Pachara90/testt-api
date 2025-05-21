<?php
// File: api/participants.php

include '../db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $event_id = $_GET['event_id'] ?? null;

        if ($event_id) {
            $stmt = $conn->prepare("SELECT * FROM participants WHERE event_id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT * FROM participants");
        }

        $participants = [];
        while ($row = $result->fetch_assoc()) {
            $participants[] = $row;
        }

        echo json_encode($participants);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->fullname) || empty($data->email) || empty($data->event_id)) {
            http_response_code(400);
            echo json_encode(["error" => "ข้อมูลไม่ครบ"]);
            exit;
        }

        // ตรวจสอบซ้ำ
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM participants WHERE event_id = ? AND email = ?");
        $stmt->bind_param("is", $data->event_id, $data->email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result['total'] > 0) {
            echo json_encode(["error" => "ลงทะเบียนซ้ำ"]);
            exit;
        }

        // ตรวจสอบจำนวนผู้เข้าร่วม
        $stmt = $conn->prepare("SELECT COUNT(*) AS current FROM participants WHERE event_id = ?");
        $stmt->bind_param("i", $data->event_id);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc()['current'];

        $stmt = $conn->prepare("SELECT max_participants FROM events WHERE id = ?");
        $stmt->bind_param("i", $data->event_id);
        $stmt->execute();
        $max = $stmt->get_result()->fetch_assoc()['max_participants'];

        if ($current >= $max) {
            echo json_encode(["error" => "จำนวนคนเต็มแล้ว"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO participants (event_id, fullname, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $data->event_id, $data->fullname, $data->email, $data->phone);
        $stmt->execute();

        echo json_encode(["success" => true]);
        break;

    case 'DELETE':
        parse_str(file_get_contents("php://input"), $params);
        $id = $params['id'] ?? null;

        if ($id) {
            $stmt = $conn->prepare("DELETE FROM participants WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            echo json_encode(["success" => true]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing participant ID"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>
