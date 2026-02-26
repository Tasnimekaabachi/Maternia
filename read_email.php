<?php
$content = file_get_contents('C:/Users/OrdiOne/Desktop/Maternia/config/google/service-account.json');
$json = json_decode($content, true);
echo "EMAIL_FOUND:[" . $json['client_email'] . "]\n";
echo "PROJECT_ID:[" . $json['project_id'] . "]\n";
