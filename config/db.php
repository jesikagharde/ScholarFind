<?php
/**
 * Database Configuration & Connection (PDO)
 * Scholarship Finder & Eligibility Checker
 */

$host     = '127.0.0.1';
$db       = 'scholarship_db';
$user     = 'root';
$pass     = ''; // Default XAMPP password is empty
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border-left: 5px solid #e74c3c; background: #fff5f5; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h2 style="color: #c0392b; margin-top: 0;">Database Connection Error</h2>
            <p>Could not connect to the database <strong>scholarship_db</strong>.</p>
            <p><strong>Common Fixes:</strong></p>
            <ol style="line-height: 1.6;">
                <li>Ensure <strong>Apache</strong> and <strong>MySQL</strong> are started in the <strong>XAMPP Control Panel</strong>.</li>
                <li>Open <a href="http://localhost/phpmyadmin" target="_blank" style="color: #2980b9;">phpMyAdmin</a> and make sure the database <code>scholarship_db</code> is created and imported from <code>database.sql</code>.</li>
            </ol>
            <p style="font-size: 12px; color: #7f8c8d;">Technical detail: ' . htmlspecialchars($e->getMessage()) . '</p>
        </div>
    ');
}
