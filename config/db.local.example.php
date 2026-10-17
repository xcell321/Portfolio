<?php
// Template para sa deployment. Kopyaha kini nga file → config/db.local.php
// dayon ilisi sa tinuod nga credentials gikan sa hosting provider nimo
// (cPanel → MySQL Databases). Ang db.local.php kay git-ignored.
//
// ALTERNATIBA (walay file): i-set ang env vars sa .htaccess,
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
