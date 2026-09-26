<?php
session_start();

function json_body(): array {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}
function out_json($x, int $code=200): void {
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($x);
    exit;
}
function user(): ?array { return $_SESSION["user"] ?? null; }
function need_login(): void {
    if (!user()) out_json(["error"=>"Login required"],401);
}
function need_admin(): void {
    need_login();
    if ((user()["role"] ?? "") !== "admin") out_json(["error"=>"Admin required"],403);
}
function is_admin(): bool { return (user()["role"] ?? "") === "admin"; }
