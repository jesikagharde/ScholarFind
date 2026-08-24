<?php
/**
 * Authentication, Session Management & ScholarFind ID Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'             => $_SESSION['user_id'],
        'name'           => $_SESSION['name'] ?? 'User',
        'email'          => $_SESSION['email'] ?? '',
        'scholarfind_id' => $_SESSION['scholarfind_id'] ?? '',
        'role'           => $_SESSION['role'] ?? 'student',
    ];
}

function require_login($redirect_to = 'login.php') {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Please log in to access this page.";
        header("Location: $redirect_to");
        exit;
    }
}

function require_admin($redirect_to = '../login.php') {
    if (!is_admin()) {
        $_SESSION['flash_error'] = "Access denied. Administrator privilege required.";
        header("Location: $redirect_to");
        exit;
    }
}

function get_student_profile($pdo, $user_id) {
    if (!$user_id) return null;
    $stmt = $pdo->prepare("SELECT * FROM student_profiles WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function generate_scholarfind_id($pdo, $name) {
    $parts = preg_split('/\s+/', trim($name));
    $first = preg_replace('/[^a-z0-9]/', '', strtolower($parts[0] ?? 'user'));
    $last = isset($parts[1]) ? preg_replace('/[^a-z0-9]/', '', strtolower($parts[1])) : '';
    if (empty($first)) $first = 'student';
    
    $base_id = !empty($last) ? "{$first}.{$last}" : $first;
    $candidate = $base_id;
    $suffix = 2;
    
    while (true) {
        $chk = $pdo->prepare("SELECT user_id FROM users WHERE scholarfind_id = ? LIMIT 1");
        $chk->execute([$candidate]);
        if (!$chk->fetch()) {
            return $candidate;
        }
        $candidate = "{$base_id}{$suffix}";
        $suffix++;
    }
}

function validate_session_user($pdo) {
    if (is_logged_in()) {
        $stmt = $pdo->prepare("SELECT user_id, name, email, scholarfind_id, role FROM users WHERE user_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['flash_error'] = "Database was updated. Please log in or register your account again.";
            header("Location: login.php");
            exit;
        } else {
            // Keep session fresh
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['scholarfind_id'] = $user['scholarfind_id'] ?? '';
            $_SESSION['role'] = $user['role'];
        }
    }
}
