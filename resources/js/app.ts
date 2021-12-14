/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const {default: router} = require('./router')
const {default: store} = require('./store')
const {default: StorageService} = require('./services/storage.service')
import ApiService from './services/api.service'
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import { faBars, faTimes, faKey, faInfo, faCheck} from '@fortawesome/free-solid-svg-icons';
import { createApp } from 'vue';

library.add(faTimes, faBars, faKey, faInfo, faCheck)

var myStorage = new StorageService('localStorage')
myStorage.setUpStorageBasedOnType()

export { myStorage }

ApiService.init(`${process.env.APP_URL || 'http://127.0.0.1:8080'}/api`)

const app = createApp({});

app
    .use(router)
    .use(store)

    .component('main-app', require('./components/MainApp.vue').default)
    .component('nav-bar', require('./components/NavBar.vue').default)
    .component('font-awesome-icon', FontAwesomeIcon)

    .mount('#app')
