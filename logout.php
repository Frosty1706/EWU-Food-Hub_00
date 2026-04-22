<?php
// File: C:\xampp\htdocs\EWU Food Hub\logout.php

session_start();
session_unset();
session_destroy();
header('Location: auth/login.php');
exit();
?>
