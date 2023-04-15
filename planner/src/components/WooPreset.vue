<template>
  <el-drawer
    class="woo-preset-drawer"
    :title="drawerTitle"
    :append-to-body="false"
    :visible.sync="cartVisible"
    direction="rtl"
    :modal-append-to-body="false"
    :modal="false"
    :size="360"
  >
    <div class="woo-preset-container">
      <div class="woo-preset-wrapper">
        <el-card
          class="woo-preset-item"
          v-for="preset of presetList"
          :key="preset.id"
          @click.native="loadPreset(preset)"
        >
          <el-image :src="preset.image_url"></el-image>
          <div class="woo-preset-info">
            <div class="woo-preset-title">{{ preset.title }}</div>
          </div>
        </el-card>
      </div>
    </div>
  </el-drawer>
</template>

<script>
import { getPresets } from "@/api/woocommerce";
/* eslint-disable */
export default {
  props: {},
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
    closePresetDrawer() {
      this.cartVisible = false;
    },
    addToPreset() {
      this.$message.error("Presets can only be edited in backend.");
      this.closePresetDrawer();
      this.$eventBus.$emit("save");
    },
    query() {
      getPresets()
        .then((result) => {
          console.log(result.data);
          this.presetList = result.data;
        })
        .catch((err) => {
          console.log("error", err);
        });
    },
    loadPreset(preset) {
      this.$confirm(
        "This action will replace current creation board with all products from the preset, confirm to continue.",
        "Alert",
        {
          confirmButtonText: "Yes",
          cancelButtonText: "No",
          // type: "info",
        }
      )
        .then(() => {
          console.log("load preset", preset);
          this.closePresetDrawer();
          this.$eventBus.$emit("loadPreset", preset);
          done();
        })
        .catch(() => {});
    },
  },
};
</script>

<style lang="scss">
.woo-preset-drawer {
  .el-drawer__header {
    margin-bottom: 0;
  }
  .woo-preset-container {
    padding: 20px 0 55px 0;
  }
  .woo-preset-wrapper {
    padding: 0 20px 0 20px;
  }
}
.woo-preset-drawer .el-drawer__body {
  position: relative;
}
.woo-preset-item {
  margin-bottom: 10px;
  cursor: pointer;
}
.woo-preset-item .el-card__body {
  position: relative;
  .woo-preset-info {
    font-size: 12px;
    position: absolute;
    bottom: 35px;
    padding: 0 20px;
    width: calc(100% - 40px);
    .woo-preset-title {
      width: 100%;
      white-space: nowrap;
      overflow: hidden;
      font-weight: bold;
      text-overflow: ellipsis;
      padding-left: 10px;
      background-color: #ffffff9c;
      padding-left: 10px;
      box-shadow: 1px 0px 2px #929292;
    }
  }
  .el-image {
    flex-grow: 0;
    width: 100%;
    height: auto;
  }
}
.woo-preset-footer {
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