/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import "./bootstrap"
import "../css/app.css"
import router from './router/index.js'
import store from './store/index.js'
import StorageService from './services/storage.service'
import ApiService from './services/api.service'

import mitt from 'mitt'
import DatePicker from '@vuepic/vue-datepicker'
import "@vuepic/vue-datepicker/dist/main.css"
import {library} from '@fortawesome/fontawesome-svg-core'
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome'
import { 
    faTimes, faKey, faInfo, faCheck, faBars, faSpinner, faUserCircle,
    faPencil, faArrowLeftLong
} from '@fortawesome/free-solid-svg-icons';
import { } from '@fortawesome/free-regular-svg-icons';
import { createApp } from 'vue/dist/vue.esm-bundler';
import MainApp from './components/MainApp.vue'
import NavBar from './components/NavBar.vue'

library.add(faTimes, faBars, faKey, faInfo, faCheck, faSpinner, faUserCircle, faArrowLeftLong, faPencil)

var myStorage = new StorageService('localStorage')
myStorage.setUpStorageBasedOnType()

export { myStorage, router }

ApiService
    .init(`${import.meta.env.APP_URL || 'http://127.0.0.1:8080'}/api`)
    .interceptResponse()

let emitter = mitt()

const app = createApp({});

app
    .use(router)
    .use(store)

app.config.globalProperties.emitter = emitter

app.component('main-app', MainApp)
app.component('nav-bar', NavBar)
app.component('font-awesome-icon', FontAwesomeIcon)
app.component('DatePicker', DatePicker)

app.mount('#app')

export default app
