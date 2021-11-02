/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const router = require('./router')
const store = require('./store')
const StorageService = require('./services/storage.service')
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import { faBars, faTimes } from '@fortawesome/free-solid-svg-icons';
import { createApp } from 'vue';

library.add(faTimes, faBars)

var myStorage = new StorageService('localStorage')
myStorage.setUpStorageBasedOnType()

export { myStorage }

const app = createApp({});

app
    .use(router)
    .use(store)

    .component('main-app', require('./components/MainApp.vue').default)
    .component('nav-bar', require('./components/NavBar.vue').default)
    .component('font-awesome', FontAwesomeIcon)

    .mount('#app')
