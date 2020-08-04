import './public-path'
import Vue from 'vue'
import bus from './bus'

window.Vue = Vue

// Declare plugin base URL for global usage
Vue.prototype.$pluginBase = 'plugins.php/whakamahereplugin'

/**
 * The following block of code is used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('../../vue/components/log', true, /PublishLogViewer$/i)

files.keys().map(key =>
    Vue.component(
        key
            .split('/')
            .pop()
            .split('.')[0],
        files(key).default
    )
);

Vue.use(bus)
