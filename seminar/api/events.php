<?php

// File: api/events.php
include '../db.php'; 
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $result = $conn->query("SELECT * FROM events");
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        echo json_encode($events);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->title) || empty($data->description) || empty($data->date) || empty($data->max_participants)) {
            http_response_code(400);
            echo json_encode(["error" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO events (title, description, date, max_participants) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $data->title, $data->description, $data->date, $data->max_participants);
        $stmt->execute();

        echo json_encode(["success" => true]);
        break;

    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        $data = json_decode(file_get_contents("php://input"));

        if ($id && !empty($data->title) && !empty($data->description) && !empty($data->date) && !empty($data->max_participants)) {
            $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, date = ?, max_participants = ? WHERE id = ?");
            $stmt->bind_param("sssii", $data->title, $data->description, $data->date, $data->max_participants, $id);
            $stmt->execute();

            echo json_encode(["success" => true]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ข้อมูลไม่ครบหรือไม่พบ ID"]);
        }
        break;

    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;

        if ($id) {
            $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            echo json_encode(["success" => true]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ไม่พบ ID"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>
