<?php
// DB connection only. Walay queries diri.

function db_connect(): ?PDO {
  static $pdo = null;
  static $done = false;
  if ($done) return $pdo;
  $done = true;
  try {
    $pdo = new PDO(
      'mysql:host=127.0.0.1;dbname=portfolio_db;charset=utf8mb4',
      'root', '',
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
  } catch (Exception $ex) {
    $pdo = null; // graceful: site mogana gihapon via fallback data
  }
  return $pdo;
}
