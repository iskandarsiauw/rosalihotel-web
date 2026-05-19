<?php
/* Legacy entry point — redirect to the new CMS app */
require_once '../includes/auth.php';
requireLogin();
header('Location: app.php');
exit;
