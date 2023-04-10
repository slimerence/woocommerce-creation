<template>
  <el-drawer
    class="woo-scene-cart-drawer"
    :title="drawerTitle"
    :append-to-body="false"
    :visible.sync="cartVisible"
    direction="rtl"
    :modal-append-to-body="false"
    :size="360"
  >
    <div class="woo-cart-container">
      <div class="woo-cart-wrapper">
        <el-card
          class="woo-cart-item"
          v-for="(scene, index) of sceneOptions"
          :key="`${scene.id}-${index}`"
          @click.native="selectAsset(scene)"
        >
          <el-image :src="scene.url"></el-image>
          <!-- <div class="woo-cart-info">
            <div class="woo-cart-title">{{ scene.name }}</div>
          </div> -->
        </el-card>
      </div>
      <!-- <div class="woo-cart-footer">
        <el-button @click="closeCartDrawer" size="small" type="success"
          >Submit</el-button
        >
        <el-button @click="closeCartDrawer" size="small" type="danger"
          >Close</el-button
        >
      </div> -->
    </div>
  </el-drawer>
</template>

<script>
import { getOptions } from "@/api/woocommerce";
export default {
  props: {},
  name: "WooCartScene",
  data() {
    return {
      cartVisible: false,
      drawerTitle: "Cart",
      sceneOptions: [],
    };
  },
  methods: {
    open(title) {
      this.drawerTitle = title;
      this.cartVisible = true;
      this.initOptions();
    },
    closeCartDrawer() {
      this.cartVisible = false;
    },
    initOptions() {
      getOptions().then((res) => {
        const data = JSON.parse(res);
        this.sceneOptions = data.images;
      });
    },
    selectAsset(asset) {
      this.$emit("change", asset);
    },
  },
};
</script>

<style lang="scss">
.woo-scene-cart-drawer {
  .el-drawer__header {
    margin-bottom: 0;
  }
  .woo-cart-container {
    padding: 20px 0 55px 0;
    height: 100%;
  }
  .woo-cart-wrapper {
    padding: 0 20px 0 20px;
    overflow: scroll;
    height: 100%;
  }
  .woo-cart-item {
    margin-bottom: 10px;
    cursor: pointer;
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
      width: 100%;
      height: 200px;
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
}
.woo-scene-cart-drawer .el-drawer__body {
  position: relative;
}
</style>