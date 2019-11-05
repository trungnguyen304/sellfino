<template>
  <div class="main-content">
  	<div class="header bg-gradient-primary pb-6">
      <div class="container-fluid">
        <div class="header-body">
          <div class="row align-items-center py-4">
            <div class="col-lg-6 col-7">
              <h6 class="h2 text-white d-inline-block mb-0">Sellfino App Store</h6>
              <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                <ol class="breadcrumb breadcrumb-links breadcrumb-dark m-0 p-0 text-sm font-weight-600 nobg">
                  <li class="breadcrumb-item"><a href="" class="text-neutral" @click.prevent="$root.view = 'apps'"><i class="fas fa-home"></i></a></li>
                </ol>
              </nav>
            </div>
            <div class="col-lg-6 col-5 text-right">
              <div class="loading text-sm font-weight-600 text-neutral" v-if="loading">Loading... <span class="badge ml-2"></span></div>
              <a href="" class="btn btn-sm btn-neutral" @click.prevent="$root.view = 'settings'">Settings</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid mt--6">
    	<div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">Activated</h3>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush" v-if="numberOfApps.activated">
                <thead class="thead-light">
                  <tr>
                    <th class="w-20">App Name</th>
                    <th class="w-30">Description</th>
                    <th class="w-20">Documentation</th>
                    <th class="w-10">Author</th>
                    <th class="w-10">Version</th>
                    <th class="w-10"></th>
                  </tr>
                </thead>
                <tbody class="list">
                  <tr v-for="(app, index) in apps" v-if="app.active" :class="{ processing: app.processing }">
                    <th scope="row">
                      <div class="media align-items-center">
                        <a class="avatar rounded-circle mr-3" @click.prevent="$root.view = app.handle + '-index'" v-if="app.settings">
                          <img :src="'/asset/apps/' + app.handle + '/icon.png'">
                        </a>
                        <a class="avatar rounded-circle mr-3" v-else>
                          <img :src="'/asset/apps/' + app.handle + '/icon.png'">
                        </a>
                        <div class="media-body">
                          <span class="name mb-0 text-sm">
                            <a href="" class="text-primary" @click.prevent="$root.view = app.handle + '-index'" v-if="app.settings">{{ app.name }}</a>
                            <span v-else>{{ app.name }}</span>
                          </span>
                        </div>
                      </div>
                    </th>
                    <td>
                      {{ app.info }}
                    </td>
                    <td>
                      <a :href="app.docs" v-if="app.docs" target="_blank">Documentation</a>
                    </td>
                    <td>
                      <a :href="app.author.url" target="_blank" v-if="app.author.url">{{ app.author.name }}</a>
                      <span v-else>{{ app.author.name }}</span>
                    </td>
                    <td>
                      {{ app.version }}
                    </td>                    
                    <td class="text-right">
                      <a class="btn btn-icon-only text-primary m-0" @click.prevent="$root.view = app.handle + '-index'" v-if="app.settings">
                        <i class="fas fa-cog"></i>
                      </a>
                      <a class="btn btn-icon-only text-danger" @click="toggle(app)">
                        <i class="fas fa-minus-circle"></i>
                      </a>
                    </td>
                  </tr>
                </tbody>
              </table>
              <table class="table align-items-center table-flush" v-else>
                <thead class="thead-light">
                  <tr>
                    <th>
                      <span v-if="loading">Loading... Please wait</span>
                      <span v-else>There are no active apps</span>
                    </th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">Disabled</h3>
            </div>
            <div class="table-responsive">
              <div class="table-responsive">
              <table class="table align-items-center table-flush" v-if="numberOfApps.disabled">
                <thead class="thead-light">
                  <tr>
                    <th class="w-20">App Name</th>
                    <th class="w-30">Description</th>
                    <th class="w-20">Documentation</th>
                    <th class="w-10">Author</th>
                    <th class="w-10">Version</th>
                    <th class="w-10"></th>
                  </tr>
                </thead>
                <tbody class="list">
                  <tr v-for="(app, index) in apps" v-if="!app.active" :class="{ processing: app.processing }">
                    <th scope="row">
                      <div class="media align-items-center">
                        <a href="#" class="avatar rounded-circle mr-3">
                          <img :src="'/asset/apps/' + app.handle + '/icon.png'">
                        </a>
                        <div class="media-body">
                          <span class="name mb-0 text-sm">{{ app.name }}</span>
                        </div>
                      </div>
                    </th>
                    <td>
                      {{ app.info }}
                    </td>
                    <td>
                      <a :href="app.docs" v-if="app.docs" target="_blank">Documentation</a>
                    </td>
                    <td>
                      <a :href="app.author.url" target="_blank" v-if="app.author.url">{{ app.author.name }}</a>
                      <span v-else>{{ app.author.name }}</span>
                    </td>
                    <td>
                      {{ app.version }}
                    </td>
                    <td class="text-right">
                      <div class="dropdown">
                        <a class="btn btn-icon-only text-success" @click="toggle(app)">
                          <i class="fas fa-plus-circle"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <table class="table align-items-center table-flush" v-else>
                <thead class="thead-light">
                  <tr>
                    <th>
                      <span v-if="loading">Loading... Please wait</span>
                      <span v-else>There are no disabled apps</span>
                    </th>
                  </tr>
                </thead>
              </table>
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
      apps: []
   }
  },
  computed: {
    numberOfApps: function() {
      activated = 0
      disabled = 0
      this.apps.forEach(function(app) {
        if (app.active) {
          activated++
        } else {
          disabled++
        }
      })
      return {
        activated: activated,
        disabled: disabled
      }
    }
  },
  methods: {
    toggle: function(app) {
      var self = this
      Vue.set(app, 'processing', true)
      
      url = '/api/apps/toggle'
      params = {
        method: 'POST',
        headers: this.$root.fetchHeaders,
        body: JSON.stringify({
          app: app.handle
        })
      }

      fetch(url, params)
      .then(errorCheck)
      .then(function(res) {
        window.location.reload(true)
      })
      .catch((error) => {
        Vue.set(app, 'processing', false)
        alert(error)
      })
    }
  },
  mounted: function() {
    var self = this

    url = '/api/apps'
    params = {
      method: 'GET',
      headers: this.$root.fetchHeaders
    }

    fetch(url, params)
    .then(errorCheck)
    .then(function(res) {
      self.apps = res
      self.loading = false
    })
    .catch((error) => {
      alert(error)
      self.loading = false
    })
  }
}
</script>