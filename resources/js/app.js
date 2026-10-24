import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue, route } from 'ziggy-js'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./pages/**/*.vue', { eager: true })
    const page = pages[`./pages/${name}.vue`]
    return page.default || page
  },
  setup({ el, App, props, plugin }) {
    const ziggy = props.initialPage.props.ziggy

    // Expose route() globally so <script setup> can use it without explicit inject
    window.route = (name, params, absolute) => route(name, params, absolute, ziggy)

    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue, ziggy)
      .mount(el)
  },
})