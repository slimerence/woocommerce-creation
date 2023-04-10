<template>
  <div :id="uniqueId" class="woo-render-item" v-click-outside="deActivate">
    <div class="woo-render-btn" @click.capture="removeItem">
      <i class="el-icon-delete"></i>
    </div>
    <div class="woo-render-image" :style="{ height: calcHeight }">
      <el-image :src="imgSrc" @load="afterLoad"></el-image>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    info: Object,
    uniqueId: String,
  },
  name: "WooRenderItem",
  data() {
    return {
      product: {},
      placeholderUrl: "",
    };
  },
  created() {
    this.product = this.info;
  },
  mounted() {},
  computed: {
    imgSrc() {
      if (this.product.selectVariation) {
        return this.product.selectVariation.image.full_src;
      } else if (this.product.images.length > 0) {
        return this.product.images[0];
      } else {
        return this.product.feature_image;
      }
    },
    calcHeight() {
      let height = "";
      const dimensions = this.product.selectVariation
        ? this.product.selectVariation.dimensions
        : this.product.dimensions;
      if (dimensions) {
        height = dimensions.height * 2.3 || "200";
      }
      return `${height}px`;
    },
  },
  methods: {
    // onLoad() {
    //   console.log("图片加载成功");
    //   const element = document.getElementById(this.id);
    //   if (!this.elements) {
    //     const xElem = this.subjx(element);
    //     this.elements = xElem.drag();
    //     console.log("elements", this.elements);
    //     this.control();
    //   }
    // },
    afterLoad() {
      const element = document.getElementById(this.uniqueId);
      this.$emit("onLoad", element, this.uniqueId);
    },
    control() {
      // const element = this.elements;
      // element.disable();
    },
    deActivate() {
      // const element = this.renderElements[productId];
      // if (element) {
      //   console.log("click outside", element);
      // }
      this.$emit("clickOut", this.uniqueId);
    },
    removeItem() {
      console.log("11111");
      this.$emit("removeEle", this.uniqueId);
    },
  },
};
</script>

<style lang="scss">
.woo-render-item {
  display: inline-flex;
  position: absolute;
  z-index: 90;
  .woo-render-image {
    line-height: 0;
    width: 100%;
    height: 100%;
    .el-image {
      width: 100%;
      height: 100%;
      overflow: hidden;
      img {
        height: 100%;
        width: auto;
      }
    }
  }
  .woo-render-btn {
    position: absolute;
    top: 0;
    right: 0;
    width: 20px;
    height: 20px;
    z-index: 100;
    background: red;
    text-align: center;
    line-height: 20px;
    i {
      font-size: 12px;
      color: #fff;
    }
  }
}
</style>