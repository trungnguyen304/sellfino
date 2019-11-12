<?php

namespace Sellfino;

use Sellfino\DB;
use Sellfino\Shopify;
use Sellfino\Helpers;
use \Firebase\JWT\JWT; 

Class Sellfino
{

  public $routes = [];
  public $app = [];
  public $apps = [];

  public function __construct()
  {

    if (isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {

      $_SESSION['shop'] = $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'];

    } else {

      $parts = parse_url($_SERVER['REQUEST_URI']);

      if (isset($parts['query'])) {

        parse_str($parts['query'], $query);
        $_SESSION['shop'] = $query['shop'];

      }

    }

    $db = DB::get('apps');
    $apps_files = Helpers::filescan(DIR_APPS);

    foreach ($apps_files as $filename) {
      
      $file = pathinfo($filename);

      if ($file['basename'] == 'info.json') {

        $app = json_decode(file_get_contents($filename), true);

        if (isset($app['private'])) {
          if (!isset($_SESSION['shop']) || !in_array($_SESSION['shop'], explode(',', $app['private']))) {
            continue;
          }
        }

        if (isset($db[$app['handle']])) {
          $app = array_merge($db[$app['handle']], $app);
        }

        $this->apps[] = $app;

        $classname = '\Apps\\' . $app[ 'handle' ] . '\\app';
        $app_class = new $classname($this);

        $this->app[$app['handle']] = $app_class;

      }

    }

  }

  public function route($action, \Closure $callback)
  {

    $action = trim($action, '/');
    $this->routes[$action] = $callback;

  }

  public function view($view, $data)
  {

    if ($view != 'install') {
      $data['assets'] = $this->getAppsAssets();
    }
    
    $path = DIR_PUBLIC . 'views/' . $view . '.php';

    ob_start();
    include($path);
    $var=ob_get_contents(); 
    ob_end_clean();

    echo $var;
    die();

  }

  public function setToken()
  {

    $tokenId    = base64_encode(uniqid());
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + 7200;
    $serverName = HOST;

    $data = [
      'iat'  => $issuedAt,
      'jti'  => $tokenId,
      'iss'  => $serverName,
      'nbf'  => $notBefore,
      'exp'  => $expire
    ];
    $secretKey = base64_decode(API_SECRET);
    return JWT::encode($data, $secretKey,'HS512'); 

  }

  public function checkToken()
  {

    $validateHttp = false;

    if (isset($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'])) {

      $access_token_path = DIR_STORES . $_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] . '/.access_token';
      $validateHttp = true;

      if (!file_exists($access_token_path)) {

        header('HTTP/1.1 401 Unauthorized', true, 401);
        die();

      }

    }

    if (isset($_SERVER['HTTP_ORIGIN']) && !$validateHttp) {

      $origin = str_replace(['https://', 'http://', 'www.'], '', $_SERVER['HTTP_ORIGIN']);
      $allowed_domains = json_decode(file_get_contents(DIR_ROOT . '.domains'), true);
      $validateHttp = true;

      if (!isset($allowed_domains[$origin])) {

        header('HTTP/1.1 401 Unauthorized', true, 401);
        die();

      }

    }

    if (!isset($_SERVER['HTTP_X_TOKEN']) || !$validateHttp) {

      header('HTTP/1.1 401 Unauthorized', true, 401);
      die();
      
    }

    try {

      $secretKey = base64_decode(API_SECRET); 
      JWT::decode($_SERVER['HTTP_X_TOKEN'], $secretKey, ['HS512']);

    } catch (Exception $e) {

      header('HTTP/1.1 401 Unauthorized', true, 401);
      die();

    }

  }

  public function addAllowedDomain($domain)
  {

    $allowed_domains_path = DIR_ROOT . '.domains';

    if (!file_exists($allowed_domains_path)) {

      file_put_contents($allowed_domains_path, '{ "' . $domain . '" : "' . $domain . '" }');

    } else {

      $domains = json_decode(file_get_contents($allowed_domains_path), true);
      $domains[$domain] = $domain;
      file_put_contents($allowed_domains_path, json_encode($domains));

    }

  }

  public function getAppsAssets()
  {

    $files = [];

    foreach ($this->apps as $app) {

      if (isset($app['active']) && $app['active']) {

        $handle = $app['handle'];
        $files = array_merge($files, Helpers::filescan(DIR_APPS . $handle . '/'));
        
      }

    }

    $assets = [];

    foreach ($files as $filename) {
      
      $file = pathinfo($filename);
      $file['dirname'] = str_replace('\\', '/', $file['dirname']);

      if (isset($file['extension']) && $file['extension'] == 'js') {

        $paths = explode('/apps/', $file['dirname']);
        $path = end($paths);

        $tag = '<script src="/asset/apps/' . $path . '/' . $file['basename'] . '"></script>';
        $assets[] = $tag;

      }

      if (isset($file['extension']) && $file['extension'] == 'css') {

        $paths = explode('/apps/', $file['dirname']);
        $path = end($paths);

        $tag = '<link rel="stylesheet" href="/asset/apps/' . $path . '/' . $file['basename'] . '">';
        $assets[] = $tag;

      }

      if (isset($file['extension']) && $file['extension'] == 'vue') {

        $paths = explode('/apps/', $file['dirname']);
        $path = end($paths);

        if ($file['filename'][0] == '_') {
          $prefix = 'inc-';
          $filename = substr($file['filename'], 1);
        } else {
          $prefix = 'view-';
          $filename = $file['filename'];
        }

        $tag = '<script>Vue.component("' . $prefix . str_replace('/views', '', $path) . '-' . $filename . '", window.httpVueLoader("/asset/apps/' . $path . '/' . $file['basename'] . '"))</script>';
        $assets[] = $tag;

      }

    }
    
    return $assets;

  }

  public function run()
  {

    $uri = explode('?', $_SERVER['REQUEST_URI']);
    $code = $_SERVER['REQUEST_METHOD'] . ' ' . $uri[0];
    $action = trim( $code, '/' );

    if (isset($this->routes[$action])) {

      $callback = $this->routes[$action];
      call_user_func($callback, $this);

    } else {

      // Show assets
      $sub = substr($action, 0, 11);

      if ($sub == 'GET /asset/') {

        $mime_types = [
          'css' => 'text/css'
          ,'js' => 'application/javascript'
          ,'png' => 'image/png'
          ,'jpg' => 'image/jpg'
          ,'gif' => 'image/gif'
          ,'json' => 'application/json'
          ,'vue' => 'text/plain'
        ];

        $ext = explode('.', $action);
        
        header('Content-Type: ' . $mime_types[end($ext)]);
        header('Status: 200');
        readfile(DIR_ROOT . str_replace($sub, '', $action));
        die();

      }

      // Route with param
      foreach ($this->routes as $route => $obj) {
        
        if (strpos($route, '@') !== false) {

          $noparam = str_replace('@', '', $route);

          if (strpos($action, $noparam) !== false) {

            $callback = $this->routes[$route];
            $param = str_replace($noparam, '', $action);

            call_user_func($callback, $this, $param);
            die();

          }
        }
      }

      Helpers::error(404);

    }

    die();

  }

}