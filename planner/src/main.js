import Vue from 'vue'
import App from './App.vue'
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import subjx from 'subjx';
import 'subjx/dist/style/subjx.css';
import '@/style/main.scss';
import locale from 'element-ui/lib/locale/lang/en'
Vue.use(ElementUI, { locale })

Vue.prototype.subjx = subjx;
Vue.prototype.$eventBus = new Vue();
Vue.config.productionTip = false

Vue.directive('click-outside', {
  bind: function (el, binding, vnode) {
    el.clickOutsideEvent = function (event) {
      // here I check that click was outside the el and his children
      if (!(el == event.target || el.contains(event.target))) {
        // and if it did, call method provided in attribute value
        // console.log('vnode',vnode);
        vnode.context[binding.expression](event);
      }
    };
    document.body.addEventListener('click', el.clickOutsideEvent)
  },
  unbind: function (el) {
    document.body.removeEventListener('click', el.clickOutsideEvent)
  },
});

Vue.use(ElementUI);

new Vue({
  render: h => h(App),
}).$mount('#app')
