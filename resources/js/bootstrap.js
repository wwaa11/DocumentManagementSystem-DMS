import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

import Swal from "sweetalert2";
window.Swal = Swal;

import jQuery from "jquery";
window.$ = window.jQuery = jQuery;
