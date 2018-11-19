<?php
$data = json_decode(file_get_contents('php://input'), true);
error_log(print_r($data, 1), 3, 'journal.log');