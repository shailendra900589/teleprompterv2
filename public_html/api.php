<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("X-Content-Type-Options: nosniff");

if (($_SERVER["REQUEST_METHOD"] ?? "") === "OPTIONS") {
    http_response_code(204);
    exit;
}

ini_set("display_errors", "0");

$file = __DIR__ . "/data.json";
$maxText = 500000;

$defaults = [
    "text" => "Welcome. Paste a script or upload a PDF.",
    "fontSize" => 45,
    "fontMode" => "manual",
    "speed" => 0,
    "scroll" => 0,
    "isMirroredX" => false,
    "isMirroredY" => false,
    "rotation" => "0deg",
    "bgColor" => "#000000",
    "textColor" => "#ffffff",
    "margin" => 20,
    "lineHeight" => 1.5,
    "textAlign" => "center",
    "cue" => 0.4,
    "countdown" => 0,
    "source" => "text",
    "sourceName" => "",
    "updatedAt" => 0,
];

function respond($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function clip_text($value, $max) {
    $text = (string) $value;
    if (function_exists("mb_substr")) {
        return mb_substr($text, 0, $max);
    }
    return substr($text, 0, $max);
}

function sanitize_color($value, $fallback) {
    $color = strtolower(trim((string) $value));
    if (preg_match("/^#[0-9a-f]{6}$/", $color)) return $color;
    if (preg_match("/^#([0-9a-f])([0-9a-f])([0-9a-f])$/", $color, $m)) {
        return "#" . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3];
    }
    return $fallback;
}

function clamp_number($value, $min, $max, $fallback, $asInt) {
    if ($value === null || $value === "") return $fallback;
    $n = is_numeric($value) ? $value + 0 : null;
    if ($n === null || !is_finite($n)) return $fallback;
    $n = max($min, min($max, $n));
    return $asInt ? (int) round($n) : round($n, 4);
}

function sanitize_state($input, $current, $maxText) {
    $next = $current;

    if (array_key_exists("text", $input)) {
        $next["text"] = clip_text($input["text"], $maxText);
    }
    if (array_key_exists("fontSize", $input)) {
        $next["fontSize"] = clamp_number($input["fontSize"], 12, 200, $current["fontSize"], true);
    }
    if (array_key_exists("fontMode", $input)) {
        $mode = (string) $input["fontMode"];
        $next["fontMode"] = ($mode === "auto" || $mode === "manual") ? $mode : $current["fontMode"];
    }
    if (array_key_exists("speed", $input)) {
        $next["speed"] = clamp_number($input["speed"], 0, 100, $current["speed"], true);
    }
    if (array_key_exists("scroll", $input)) {
        $next["scroll"] = clamp_number($input["scroll"], 0, 1, $current["scroll"], false);
    }
    if (array_key_exists("margin", $input)) {
        $next["margin"] = clamp_number($input["margin"], 0, 240, $current["margin"], true);
    }
    if (array_key_exists("lineHeight", $input)) {
        $next["lineHeight"] = clamp_number($input["lineHeight"], 1.1, 2.4, $current["lineHeight"], false);
    }
    if (array_key_exists("cue", $input)) {
        $next["cue"] = clamp_number($input["cue"], 0.2, 0.7, $current["cue"], false);
    }
    if (array_key_exists("countdown", $input)) {
        $next["countdown"] = clamp_number($input["countdown"], 0, 10, $current["countdown"], true);
    }
    if (array_key_exists("rotation", $input)) {
        $allowed = ["0deg", "90deg", "-90deg", "180deg"];
        $rotation = (string) $input["rotation"];
        $next["rotation"] = in_array($rotation, $allowed, true) ? $rotation : $current["rotation"];
    }
    if (array_key_exists("textAlign", $input)) {
        $align = (string) $input["textAlign"];
        $next["textAlign"] = in_array($align, ["left", "center", "right"], true) ? $align : $current["textAlign"];
    }
    if (array_key_exists("bgColor", $input)) {
        $next["bgColor"] = sanitize_color($input["bgColor"], $current["bgColor"]);
    }
    if (array_key_exists("textColor", $input)) {
        $next["textColor"] = sanitize_color($input["textColor"], $current["textColor"]);
    }
    foreach (["isMirroredX", "isMirroredY"] as $flag) {
        if (array_key_exists($flag, $input)) {
            $value = $input[$flag];
            $next[$flag] = $value === true || $value === 1 || $value === "1" || $value === "true";
        }
    }
    if (array_key_exists("source", $input)) {
        $source = (string) $input["source"];
        $next["source"] = in_array($source, ["text", "pdf", "txt"], true) ? $source : "text";
    }
    if (array_key_exists("sourceName", $input)) {
        $next["sourceName"] = clip_text($input["sourceName"], 180);
    }

    $next["updatedAt"] = time();
    return $next;
}

function read_state($file, $defaults) {
    if (!file_exists($file)) return $defaults;
    $raw = file_get_contents($file);
    $json = json_decode($raw, true);
    if (!is_array($json)) return $defaults;
    return array_merge($defaults, $json);
}

function write_state($file, $defaults, $input, $maxText) {
    $fp = fopen($file, "c+");
    if (!$fp) return [false, "Could not open data file"];
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return [false, "Could not lock data file"];
    }

    $raw = stream_get_contents($fp);
    $current = $defaults;
    if ($raw) {
        $json = json_decode($raw, true);
        if (is_array($json)) $current = array_merge($defaults, $json);
    }

    $next = sanitize_state($input, $current, $maxText);
    $encoded = json_encode($next, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return [false, "Could not encode data"];
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $encoded);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return [true, $next];
}

if (!file_exists($file)) {
    file_put_contents($file, json_encode($defaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

$action = $_GET["action"] ?? "read";

if ($action === "read" || $action === "ping") {
    if ($action === "ping") respond(["status" => "ok"]);
    respond(read_state($file, $defaults));
}

if ($action === "write") {
    if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
        respond(["status" => "error", "message" => "POST required"], 405);
    }
    $input = json_decode(file_get_contents("php://input"), true);
    if (!is_array($input)) respond(["status" => "error", "message" => "Invalid JSON"], 400);

    [$ok, $result] = write_state($file, $defaults, $input, $maxText);
    if (!$ok) respond(["status" => "error", "message" => $result], 500);
    respond(["status" => "success", "updatedAt" => $result["updatedAt"]]);
}

respond(["status" => "error", "message" => "Unknown action"], 400);
