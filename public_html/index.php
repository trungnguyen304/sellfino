<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, x-xsrf-token, x_csrftoken, Cache-Control, X-Requested-With, _token');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__ . '/../');
$dotenv->load();

define('DIR_PUBLIC', __DIR__ . '/');
define('DIR_ROOT', __DIR__ . '/../');
define('DIR_SELLFINO', __DIR__ . '/../sellfino/');
define('DIR_APPS', __DIR__ . '/../apps/');
define('DIR_STORES', __DIR__ . '/../stores/');
define('DIR_QUEUE', __DIR__ . '/../queue/');

// Get keys from App settings in Partner Dashboard
define('API_VERSION', '2019-10');
define('API_KEY', getenv('API_KEY'));
define('API_SECRET', getenv('API_SECRET'));
define('HOST', getenv('HOST'));

// Scopes for apps: remove or add depending on your needs
define('SCOPE', getenv('SCOPE'));

session_start();

use Sellfino\Helpers;
use Sellfino\DB;
use Sellfino\Shopify;
use Sellfino\Sellfino;

$sellfino = new Sellfino();

$sellfino->route('GET /', function($sellfino) {

  $params = $_GET;
  $shop = (isset($params['shop']) ? $params['shop'] : null);

  if (!Shopify::validateShopDomain($shop)) {

    return $sellfino->view('install', ['host' => HOST]);

  }

  $access_token_path = DIR_STORES . $params['shop'] . '/.access_token';

  if (file_exists($access_token_path)) {

    $token = $sellfino->setToken();

    $sellfino->view('admin', ['shop' => $params['shop'], 'host' => HOST, '_token' => $token]);

  }

  $redirectUri = HOST . '/auth/shopify/callback';
  $installUrl = 'https://' . $params['shop'] . '/admin/oauth/authorize?client_id=' . API_KEY . '&scope=' . SCOPE . '&redirect_uri=' . $redirectUri;

  Helpers::redirect($installUrl);


});

$sellfino->route('GET /auth/shopify/callback', function($sellfino) {

  $params = $_GET;
  $valid_hmac = Shopify::validateHmac($params, API_SECRET);
  $valid_shop = Shopify::validateShopDomain($params['shop']);
  $access_token = '';

  if ($valid_hmac && $valid_shop) {

    $access_token = Shopify::getAccessToken($params['shop'], $params['code']);

  } else {

    // This request is NOT from Shopify
    Helpers::error(404);

  }

  if (!file_exists(DIR_STORES . $params['shop'])) {

    mkdir(DIR_STORES . $params['shop'], 0777, true);
    
  }

  file_put_contents(DIR_STORES . $params['shop'] . '/.access_token', $access_token);
  file_put_contents(DIR_STORES . $params['shop'] . '/db.json', json_encode(['settings' => []]));

  $sellfino->addAllowedDomain($params['shop']);

  $homeUrl = HOST . '/?aftercallback=true&shop=' . $params['shop'];
  Helpers::redirect($homeUrl);

});

$sellfino->route('GET /db/@', function($sellfino, $table) {

  $sellfino->checkToken();

  Helpers::json(DB::get($table));

});

$sellfino->route('POST /db/@', function($sellfino, $table) {

  $sellfino->checkToken();

  $data = json_decode(file_get_contents('php://input'), true);

  DB::put($table, $data);

  Helpers::success();

});

$sellfino->route('GET /api/apps', function($sellfino) {

  $sellfino->checkToken();
  
  Helpers::json($sellfino->apps);

});

$sellfino->route('POST /api/apps/toggle', function($sellfino) {

  $sellfino->checkToken();

  $data = json_decode(file_get_contents('php://input'), true);
  $app_handle = $data['app'];

  $db = DB::get('apps');

  if (!isset($db[$app_handle])) {

    $db[$app_handle] = ['active' => true];

  } else {

    $db[$app_handle]['active'] = !$db[$app_handle]['active'];

  }

  DB::put('apps', $db);

  if ($db[$app_handle]['active']) {

    $sellfino->app[$app_handle]->install();

  } else {

    $sellfino->app[$app_handle]->uninstall();

  }

  Helpers::success();

});

$sellfino->route('POST /api/shopify', function($sellfino) {

  $sellfino->checkToken();

  $data = json_decode(file_get_contents('php://input'), true);

  if (isset($data['endpoint'])) {

    $request = Shopify::request($data['endpoint'], $data['params'], $data['method']);

    Helpers::json(json_decode($request));

  }

  Helpers::error(404);

});

$sellfino->route('GET /app/@', function($sellfino, $url) {

  if (isset($_SESSION['shop'])) {

    $sellfino->checkToken();

    $paths = explode('/', $url);
    $app = $paths[0];
    array_shift($paths);

    $active = false;

    foreach ($sellfino->apps as $row) {
      if ($row['handle'] == $app) {
        if ($row['active']) {
          $active = true;
        }
      }
    }

    if ($active) {

      $sellfino->app[$app]->router(join($paths, '/'));

    } else {

      Helpers::error(404);

    }

  }

  Helpers::success();

});

$sellfino->route('POST /app/@', function($sellfino, $url) {

  if (isset($_SESSION['shop'])) {

    $paths = array_filter(explode('/', $url));

    if (isset($paths[1]) && $paths[1] == 'webhook') {

      Shopify::validateWebhook();

    } else {

      $origin = str_replace(['https://', 'http://', 'www.'], '', $_SERVER['HTTP_ORIGIN']);
      $host = str_replace(['https://', 'http://', 'www.'], '', HOST);
      $domains = json_decode(file_get_contents(DIR_ROOT . '.domains'), true);

      if ($host != $origin) {
        if (!isset($domains[$origin]) || $domains[$origin] != $_SESSION['shop']) {
          Helpers::error(401);
        }
      }  

    }

    $app = $paths[0];
    array_shift($paths);

    $active = false;

    foreach ($sellfino->apps as $row) {
      if ($row['handle'] == $app) {
        if ($row['active']) {
          $active = true;
        }
      }
    }

    if ($active) {

      $sellfino->app[$app]->router(join($paths, '/'));

    } else {

      Helpers::error(404);

    }

  }

  Helpers::success();

});

$sellfino->run();