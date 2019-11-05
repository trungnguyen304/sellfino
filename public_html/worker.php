<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

use Sellfino\Helpers;
use Sellfino\Shopify;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('DIR_STORES', __DIR__ . '/../stores/');
define('DIR_QUEUE', __DIR__ . '/../queue/');

$files = Helpers::filescan(DIR_QUEUE);

$found = false;
foreach ($files as $filename) {
  $fileinfo = pathinfo($filename);

  if ($fileinfo['filename'][0] != '_' && !$found) {
    $found = $filename;
  }
}

if ($found) {
  $fileinfo = pathinfo($found);

  if ($fileinfo['filename'][0] != '_') {
    $rename = '_' . $fileinfo['basename'];
    $newpath = $fileinfo['dirname'] . '/' . $rename;
    rename($filename, $newpath);

    $data = json_decode(file_get_contents($newpath), true);

    $_SESSION['shop'] = $data['shop'];

    switch ($data['type']) {
      case 'email':

        $sent = true;

        try {
          Helpers::email($data['data']);
        } catch (Exception $e) {
          $sent = false;
        }

        if ($sent) {
          unlink($newpath);
        }

      break;

    }

  }
}