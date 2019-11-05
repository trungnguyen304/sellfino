<?php

$host = $data['host'];
$shop = $data['shop'];
$_token = $data['_token'];

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://kit.fontawesome.com/c92b81ac9c.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/assets/css/argon-dashboard.min.css" />
  <link rel="stylesheet" href="/assets/css/sellfino.css" />
  <script src="/assets/js/vue-dev.js"></script>
  <!-- <script src="/assets/js/vue.js"></script> -->
  <script src="/assets/js/vue-loader.js"></script>
  <script src="/assets/js/shopify.js"></script>
  <script src="/assets/js/sortable.js"></script>

  <script>Vue.component('view-apps', window.httpVueLoader('/views/inc/apps.vue'))</script>
  <script>Vue.component('view-settings', window.httpVueLoader('/views/inc/settings.vue'))</script>

  <?php if(isset($data['assets'])) foreach ($data['assets'] as $asset) echo $asset; ?>

  <title>Sellfino - Open Source Shopify App Store</title>

  <script>

    if (window.top == window.self) {
      window.location.assign(`https://<?php echo $shop; ?>/admin/apps`);
    }

    window.shopURL = 'https://<?php echo $shop; ?>';
    window.xdomain = '<?php echo $shop; ?>';
    window._token = '<?php echo $_token; ?>';

  </script>
</head>
<body>

  <div id="root">
    <component :is="'view-' + view"></component>
    <div class="toast" :class="{ active: toast, alert: toast.error }">
      {{ toast.message }}
    </div>
  </div>

  <script src="/assets/js/platform.js"></script>

</body>
</html>