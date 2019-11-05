<?php

namespace Sellfino;

Class DB
{
  
  public static function get($table = null, $db = 'db.json') {
    
   if (!isset($_SESSION['shop'])) {

      return [];

    }
    
    $path = DIR_STORES . $_SESSION['shop'] . '/' . $db;

    if (!file_exists($path)) {

      file_put_contents($path, '');

    }
    
    $string = file_get_contents($path);
    $json = json_decode($string, true);

    if ( $table ) {

      return isset( $json[$table] ) ? $json[$table] : [];

    } else {

      return $json;

    }

  }

  public static function put($table, $data, $db = 'db.json') {

    if (!isset($_SESSION['shop'])) {

      return;

    }

    $path = DIR_STORES . $_SESSION['shop'] . '/' . $db;

    if (!file_exists($path)) {

      file_put_contents($path, '');

    }

    $string = file_get_contents($path);
    $json = json_decode($string, true);

    $json[$table] = $data;

    file_put_contents($path , json_encode($json));

  }

  public static function queue($params) {

    file_put_contents(DIR_QUEUE . '/' . $shop . '_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.json', json_encode($params));

  }

}