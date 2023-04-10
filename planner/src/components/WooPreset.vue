<template>
  <el-drawer
    class="woo-cart-drawer"
    :title="drawerTitle"
    :append-to-body="false"
    :visible.sync="cartVisible"
    direction="rtl"
    :modal-append-to-body="false"
    :modal="false"
    :size="360"
  >
    <div class="woo-cart-container">
      <div class="woo-cart-wrapper">
        <el-card
          class="woo-cart-item"
          v-for="(product, index) of products"
          :key="`${product.id}-${index}`"
        >
          <el-image
            v-if="product.images && product.images.length > 0"
            :src="product.images[0].src"
          ></el-image>
          <el-image v-else :src="placeholderUrl"> </el-image>
          <div class="woo-cart-info">
            <div class="woo-cart-title">{{ product.name }}</div>
            <div class="woo-cart-price" v-html="product.price_html"></div>
          </div>
        </el-card>
      </div>
      <div class="woo-cart-footer">
        <el-button @click="addToPreset" size="small" type="success"
          >ADD</el-button
        >
      </div>
    </div>
  </el-drawer>
</template>

<script>
import { getPresets } from "@/api/woocommerce";
/* eslint-disable */
export default {
  props: {
    products: Array,
  },
  name: "WooPreset",
  data() {
    return {
      cartVisible: false,
      drawerTitle: "Presets",
      config: null,
      presetList: [],
    };
  },
  methods: {
    open(title, config = null) {
      this.drawerTitle = title;
      this.config = config;
      this.cartVisible = true;
      this.query();
    },
    closeCartDrawer() {
      this.cartVisible = false;
    },
    addToPreset() {
      this.$message.error('Presets can only be edited in backend.');
    },
    query() {
      getPresets()
        .then((result) => {
          this.presetList = result;
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
  },
};
</script>

<style lang="scss">
.woo-cart-drawer {
  .el-drawer__header {
    margin-bottom: 0;
  }
  .woo-cart-container {
    padding: 20px 0 55px 0;
  }
  .woo-cart-wrapper {
    padding: 0 20px 0 20px;
  }
}
.woo-cart-drawer .el-drawer__body {
  position: relative;
}
.woo-cart-item {
  margin-bottom: 10px;
}
.woo-cart-item .el-card__body {
  position: relative;
  display: flex;
  gap: 10px;
  .woo-cart-info {
    flex-grow: 1;
    font-size: 12px;
    .woo-cart-title {
      width: 200px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }

  .el-image {
    flex-grow: 0;
    width: 60px;
    height: 60px;
    border: 1px solid #e3e3e3;
  }
}
.woo-cart-footer {
  position: absolute;
  bottom: 0;
  width: 100%;
  left: 0;
  padding: 10px 20px;
  box-sizing: border-box;
  background-color: #fff;
  z-index: 9;
  height: 55px;
  .el-button {
    float: right;
  }
  .el-button + .el-button {
    margin-right: 10px;
  }
}
</style>