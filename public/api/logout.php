<?php
session_start();
session_destroy();
header("Location: ../../Views/login_register.html");
exit;