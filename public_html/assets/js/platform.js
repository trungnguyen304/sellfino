function dump(data) {
  console.log(JSON.parse(JSON.stringify(data)))
}
function errorCheck(res) {
  if (res.ok) {
    return res.json()
  } else {
    throw new Error(res.status + ' : ' + res.statusText);
  }      
}

Vue.prototype.shopURL = window.shopURL
Vue.prototype.Shopify = Shopify
Vue.prototype.img_url = function(src, size = 'small') {
  split = src.split('.')
  split[split.length - 2] = split[split.length - 2] + '_' + size
  return split.join('.')
}
Vue.prototype.admin_link = function(id, handle) {
  if (handle.indexOf('collections') != -1) {
    handle = handle.split('_')[1]
  }
  return shopURL + '/admin/' + handle + '/' + id
}

new Vue({
  el: '#root',
  data: {
    view: 'apps',
    viewData: null,
    viewPrevData: null,
    toast: false,
    shopURL: shopURL,
    Sortable: Sortable,
    fetchHeaders: new Headers({
      'X-Shopify-Shop-Domain': window.xdomain,
      'X-Token': window.xtoken,
    })
  },
  watch: {
    view: function() {
      this.toast = false
    }
  },
  methods: {
    showToast: function(msg, error = false) {
      self = this
      this.toast = {
        message: msg,
        error: error
      }
      setTimeout(function() {
        self.toast = false
      }, 3000)
    }
  }
})