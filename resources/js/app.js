import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import Layout from "./Pages/Layout.vue";
import '../css/app.css'

import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })

        //return pages[`./Pages/${name}.vue`]
        let pageKey = Object.keys(pages).find(key => key.endsWith(`/${name}.vue`));
        let page = pages[pageKey];

        page.default.layout = page.default.layout || Layout

        return page
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
