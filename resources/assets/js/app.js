
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue';
import Vuex from 'vuex';
import VueSocket from 'vue-native-websocket';
Vue.use(Vuex);

window.token = $('meta[name="csrf-token"]').attr('content');

var store = new Vuex.Store({
    state: {
        users: null,
        messages: null,
        socket: {
            isConnected: false,
            message: '',
            reconnectError: false,
        }
    },
    mutations: {
        SOCKET_ONOPEN (state, event)  {
            state.socket.isConnected = true
            console.log(event);
        },
        SOCKET_ONCLOSE (state, event)  {
            state.socket.isConnected = false
            console.log(event);
        },
        SOCKET_ONERROR (state, event)  {
            console.error(state, event)
        },
        // default handler called for all methods
        SOCKET_ONMESSAGE (state, message)  {
            console.log(message);
            state.socket.message = message
        },
        // mutations for reconnect methods
        SOCKET_RECONNECT(state, count) {
            console.info(state, count)
        },
        SOCKET_RECONNECT_ERROR(state) {
            state.socket.reconnectError = true;
        },
    },
    actions: {

    },
    getters: {
        usersOnline: state => {
            return state.users.length;
        }
    }
});


Vue.use(VueSocket, 'ws://localhost:8080?' + token, { store: store, format: 'json' }); //Create websocket connection with token

store.$socket = Vue.prototype.$socket;

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue'));

const app = new Vue({
    el: '#app',
    store,
    data: {
        input: null
    },
    methods: {
        clickButton: function(val) {
            // $socket is [WebSocket](https://developer.mozilla.org/en-US/docs/Web/API/WebSocket) instance
            if(this.input.length > 0) this.$socket.send(JSON.stringify({type: 'message', text: this.input}));
            this.input = null;
            // or with {format: 'json'} enabled
            console.log(this.$socket);
            //this.$socket.sendObj({type: 'message', text: "Hello!"});
        }
    },
    mounted() {

    }
});
