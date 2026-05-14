<?php
function api_payload(): array
{
    $payload = $_POST;
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            $payload = array_merge($payload, $decoded);
        }
    }

    return $payload;
}

function api_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function api_success(array $data = [], string $message = 'OK'): void
{
    api_response([
        "status" => "success",
        "message" => $message,
        "data" => $data,
    ]);
}

function api_error(string $message, int $statusCode = 400, array $data = []): void
{
    api_response([
        "status" => "error",
        "message" => $message,
        "data" => $data,
    ], $statusCode);
}
?>
