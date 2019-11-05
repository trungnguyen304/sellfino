<template>
  <div class="main-content">
    <div class="header bg-gradient-primary" :class="{ 'pb-6' : !loading }">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Sellfino App Store</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark m-0 p-0 text-sm font-weight-600 nobg">
                  <li class="breadcrumb-item"><a href="" class="text-neutral" @click.prevent="$root.view = 'apps'"><i class="fas fa-home"></i></a></li>
                  <li class="breadcrumb-item active text-light">General Settings</li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <div class="loading text-sm font-weight-600 text-neutral" v-if="loading || saving">Loading... <span class="badge ml-2"></span></div>
              <a href="" class="btn btn-sm btn-neutral" v-if="!loading" :class="{ disabled: saving }" @click.prevent="save">Save</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid mt--6" v-if="!loading">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">SMTP <p class="m-0"><small>Provide SMTP details to use email messages in apps. Without proper configuration, no email will be sent through the platform.</small></p></h3>
            </div>
            <div class="card-body pt-0">
              <div class="row">
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-from-email" class="form-control-label">From Email</label>
                    <input type="text" id="smtp-from-email" class="form-control" v-model="settings.smtp.from">
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-from-name" class="form-control-label">From Name</label>
                    <input type="text" id="smtp-from-name" class="form-control" v-model="settings.smtp.name">
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-host" class="form-control-label">Host</label>
                    <input type="text" id="smtp-host" class="form-control" v-model="settings.smtp.host">
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-port" class="form-control-label">Port</label>
                    <input type="text" id="smtp-port" class="form-control" v-model="settings.smtp.port">
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-encryption" class="form-control-label">Encryption</label>
                    <select id="smtp-encryption" class="form-control" v-model="settings.smtp.encryption">
                      <option value="">No encryption</option>
                      <option value="ssl">SSL Encryption</option>
                      <option value="tls">TLS Encryption</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-authentication" class="form-control-label">Authentication</label>
                    <select id="smtp-authentication" class="form-control" v-model="settings.smtp.authentication">
                      <option value="0">No authentication</option>
                      <option value="1">Yes, use authentication</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-username" class="form-control-label">Username</label>
                    <input type="text" id="smtp-username" class="form-control" v-model="settings.smtp.username">
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="smtp-password" class="form-control-label">Password</label>
                    <input type="password" id="smtp-password" class="form-control" v-model="settings.smtp.password">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
module.exports = {
  data: function() {
    return {
      loading: true,
      saving: false,
      settings: {
        smtp: {}
      }
   }
  },
  methods: {
    save: function() {
      var self = this
      this.saving = true

      url = '/db/settings'
      params = {
        method: 'POST',
        headers: this.$root.fetchHeaders,
        body: JSON.stringify(this.settings)
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        self.saving = false
        self.$root.showToast('Settings saved')
      })
      .catch((error) => {
        alert(error)
      })
    }
  },
  mounted: function() {
    var self = this

    url = '/db/settings'
    params = {
      method: 'GET',
      headers: this.$root.fetchHeaders
    }

    fetch(url, params)
    .then(errorCheck)
    .then(function(res) {
      if (!res.smtp) {
        res.smtp = {
          from: '', name: '', host:'', port: '', encryption: '',
          authentication: '', username: '', password: ''
        }
      }
      Vue.set(self, 'settings', Object.assign({},res))
      self.loading = false
    })
    .catch((error) => {
      alert(error)
      self.loading = false
    })
  }
}
</script>