<template>
  <div
    id="creationBoard"
    class="woo-board"
    @click.self="clickBoard"
    :style="{ backgroundImage: background }"
    v-loading="saveLoading"
    element-loading-text="Saving your creation..."
    element-loading-spinner="el-icon-loading"
    element-loading-background="rgba(0, 0, 0, 0.8)"
  >
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
import html2canvas from "html2canvas";
import { dataURItoBlob, uploadFile } from "@/utils/upload";
import { savePresetPosts } from "@/api/woocommerce";
import moment from "moment";
import Cookies from "js-cookie";
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
      saveLoading: false,
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
          if (this.asset.backgroundUrl) {
            this.background = this.asset.backgroundUrl;
          } else {
            this.background = `url('${this.asset.url}')`;
          }
        }
      },
    },
  },
  mounted() {
    this.$eventBus.$on("save", () => {
      this.saveBoard("preset");
    });
    this.$eventBus.$on("saveCart", () => {
      this.saveBoard("cart");
    });
  },
  methods: {
    loadProducts(productList) {
      this.products = productList;
    },
    preCheckPreset() {
      return this.products.length > 0;
    },
    saveBoard(type = "preset") {
      this.saveLoading = true;
      const valid = this.preCheckPreset();
      // return;
      if (!valid) {
        this.$message.info(
          "Please add at least one product to the creation board"
        );
        this.saveLoading = false;
        return;
      } else {
        // 执行保存
        this.disableAll();
        const board = document.getElementById("creationBoard");
        html2canvas(board).then(async (canvas) => {
          const dataUrl = canvas.toDataURL("image/png");
          const blob = dataURItoBlob(dataUrl);
          const url = await uploadFile(blob);
          if (type == "preset") {
            this.saveToPresetPost(url);
          } else {
            Cookies.set("creation_url", url);
            this.$eventBus.$emit("addToCart");
          }
        });
      }
    },
    saveToPresetPost(url) {
      const configText = this.generateRenderInfo();
      const timeName = moment().format("MMMM Do YYYY, HH:mm:ss");
      const data = {
        title: timeName,
        content: JSON.stringify(configText),
        image: url,
      };
      savePresetPosts(data)
        .then((res) => {
          console.log("res", res);
          if (res.success) {
            this.$message.success("Save Success");
          }
        })
        .finally(() => {
          this.saveLoading = false;
          this.$eventBus.$emit("presetSaved");
        });
    },
    generateRenderInfo() {
      const renderProducts = this.products.map((product) => {
        const element = document.getElementById(product.key);
        console.log("element", element);
        return {
          style: element ? element.style.transform : "",
          ...product,
        };
      });
      const config = {
        background: this.background,
        products: renderProducts,
      };
      return config;
    },
    productLoad(product, uniqueId) {
      console.log("load item", product, uniqueId);
      if (product.style && product.style != "") {
        const element = document.getElementById(uniqueId);
        if (element) {
          element.style.transform = product.style;
        }
      }
      // init
      this.enableDrag(uniqueId, true);
    },
    disableAll() {
      for (let key in this.renderElements) {
        this.clickOutSide(key);
      }
    },
    enableDrag(uniqueId, resize = false) {
      this.disableAll();
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
    clickBoard() {
      this.disableAll();
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

.woo-render-item .woo-render-btn {
  display: none;
}

.woo-render-item.sjx-drag .woo-render-btn {
  display: block;
}
</style>