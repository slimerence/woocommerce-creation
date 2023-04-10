<template>
  <div class="woo-board" :style="{ backgroundImage: background }">
    <woo-render-item
      v-for="product of products"
      :key="product.key"
      :uniqueId="product.key"
      :info="product"
      @onLoad="productLoad"
      @removeEle="removeEle"
      @click.native="activateElement(product.key)"
    ></woo-render-item>
  </div>
</template>

<script>
// import mock from "@/utils/mock";
import WooRenderItem from "./WooItem.vue";
export default {
  props: {
    productList: Array,
    asset: Object,
  },
  components: { WooRenderItem },
  name: "WooBoard",
  data() {
    return {
      products: [],
      xElements: [],
      renderElements: {},
      background: "",
    };
  },
  watch: {
    productList: {
      deep: true,
      handler() {
        this.products = this.productList;
      },
    },
    asset: {
      handler() {
        if (this.asset) {
          this.background = `url('${this.asset.url}')`;
        }
      },
    },
  },
  methods: {
    productLoad(item, uniqueId) {
      console.log("load item", item, uniqueId);
      // init
      this.enableDrag(uniqueId, true);
    },
    enableDrag(uniqueId, resize = false) {
      for (let key in this.renderElements) {
        this.clickOutSide(key);
      }
      const element = document.getElementById(uniqueId);
      if (element && !this.renderElements[uniqueId]) {
        const options = {
          proportions: true,
          resizable: false,
        };
        const xElem = this.subjx(element);
        const dragItem = xElem.drag(options);
        this.renderElements[uniqueId] = dragItem;
        if (resize) {
          // dx, // resize along the x axis
          // dy, // resize along the y axis
          // revX, // reverse resizing along the x axis
          // revY, // reverse resizing along the y axis
          // doW, // allow width resizing
          // doH  // allow height resizing
          // console.log("resize now", element, dragItem.storage);
          // const currentWidth = 300 - element.clientWidth;
          // dragItem.exeResize({
          //   dx: currentWidth,
          // });
          dragItem.fitControlsToSize();
        }
      }
    },
    clickOutSide(uniqueId) {
      // console.log("id", uniqueId);
      const element = this.renderElements[uniqueId];
      if (element) {
        element.disable();
        delete this.renderElements[uniqueId];
      }
    },
    activateElement(uniqueId) {
      this.enableDrag(uniqueId);
    },
    removeEle(uniqueId) {
      const element = this.renderElements[uniqueId];
      if (element) {
        element.disable();
        delete this.renderElements[uniqueId];
      }
      // this.products = this.products.filter(product=>product.key !==uniqueId)
      this.$emit("removeProduct", uniqueId);
    },
  },
};
</script>

<style lang="scss">
.woo-board {
  width: 100%;
  height: 100%;
  position: relative;
  background-size: 100% 100%;
  background-repeat: no-repeat;
  background-position: bottom center;
  overflow: hidden;
}
.sjx-wrapper {
  z-index: 50;
}
.sjx-controls {
  z-index: 500;
}
</style>