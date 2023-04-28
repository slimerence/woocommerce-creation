<template>
  <div :id="uniqueId" class="woo-render-item" v-click-outside="deActivate">
    <div class="woo-render-btn-group">
      <div class="woo-render-btn bg-red" @click.capture="removeItem">
        <i class="el-icon-delete"></i>
      </div>
      <div
        class="woo-render-btn bg-yellow"
        style="transform: rotate(90deg)"
        @click.capture="flipItem"
      >
        <i class="el-icon-sort"></i>
      </div>
      <el-popover v-if="enableEdit" placement="right" width="200" trigger="click">
        <label for="imageHue">Color Change</label>
        <el-slider
          name="imageHue"
          v-model="hueRotateValue"
          :show-tooltip="false"
          :min="-180"
          :max="180"
        ></el-slider>

        <div class="woo-render-btn bg-color" slot="reference">
          <i class="el-icon-edit-outline"></i>
        </div>
      </el-popover>
    </div>

    <div
      class="woo-render-image"
      :style="{ height: calcHeight, filter: filterStyle }"
    >
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
      hueRotateValue: 0,
    };
  },
  created() {
    this.product = this.info;
  },
  mounted() {},
  computed: {
    enableEdit(){
      return this.product.method === 'sale'
    },
    filterStyle() {
      return `hue-rotate(${this.hueRotateValue}deg)`;
    },
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
      // const element = document.getElementById(this.uniqueId);
      this.$emit("onLoad", this.product, this.uniqueId);
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
      this.$emit("removeEle", this.uniqueId);
    },
    flipItem() {
      function flipMatrix3dString(matrixString) {
        let NumberAry = matrixString
          .substring(matrixString.indexOf("(") + 1, matrixString.length - 1)
          .split(",");
        NumberAry[0] *= -1;
        return `matrix3d(${NumberAry.join(",")})`;
      }
      const element = document.getElementById(this.uniqueId);
      if (element) {
        const origionalTransform = element.style.transform;
        const fliped = flipMatrix3dString(origionalTransform);
        element.style.transform = fliped;
      }
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
  .woo-render-btn-group {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 100;
  }
  .woo-render-btn {
    width: 20px;
    height: 20px;
    text-align: center;
    line-height: 20px;
    cursor: pointer;
    i {
      font-size: 12px;
      color: #fff;
    }
    &.bg-red {
      background: red;
    }
    &.bg-yellow {
      background: #efd949;
    }
    &.bg-color {
      background: linear-gradient(45deg, #79cfff, #da30b5);
    }
  }
}
</style>