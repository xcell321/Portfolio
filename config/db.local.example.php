<?php
// Deployment template. Copy this file to config/db.local.php
// then fill in the real credentials from your hosting provider
// (cPanel → MySQL Databases). db.local.php is git-ignored.
//
// ALTERNATIVE (no file): set the env vars in .htaccess,
//   SetEnv DB_HOST sql123.epizy.com
//   SetEnv DB_NAME epiz_123_portfolio
//   SetEnv DB_USER epiz_123
//   SetEnv DB_PASS secret
return [
  'host' => 'localhost',       // e.g. sql123.epizy.com
  'name' => 'portfolio_db',    // e.g. epiz_123_portfolio
  'user' => 'root',            // e.g. epiz_123
  'pass' => 'CHANGE_ME',
  'port' => '',                // optional, e.g. '3306'
];
