<?php

require_once __DIR__ . "/../includes/auth.php";

cardcms_auth_logout();

header("Location: login.php");
exit;
