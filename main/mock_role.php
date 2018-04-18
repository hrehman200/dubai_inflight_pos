<?php
require_once '../connect.php';

// only management can mock
if($_SESSION['SESS_LAST_NAME'] == ROLE_MANAGEMENT) {
    if($_GET['r'] !== ROLE_MANAGEMENT) {
        $_SESSION[SESS_MOCK_ROLE] = $_GET['r'];
    } else {
        unset($_SESSION[SESS_MOCK_ROLE]);
    }
}

$goto_page = $_SERVER['HTTP_REFERER'];
$filename =pathinfo($goto_page, PATHINFO_FILENAME);
if(in_array($filename, $_ROLE_ALLOWED_PAGES[$_SESSION['SESS_LAST_NAME']])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php');
}

