
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue';
import Vuex from 'vuex';

window.token = $('meta[name="csrf-token"]').attr('content');

console.log(token);

Vue.use(Vuex);

var store = new Vuex.Store({
    state: {
        users: null,
        messages: null,
    },
    mutations: {
    },
    actions: {

    },
    getters: {
        usersOnline: state => {
            return state.users.length;
        }
    }

});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue'));

const app = new Vue({
    el: '#app',
    store
});
