<template>
  <div id="woo-planner">
    <div class="woo-planner-container">
      <div class="woo-setting-info">
        <div class="setting-detail">
          <!-- <i class="el-icon-setting" @click="changeSetting"></i> -->
          <span
            ><strong>Event Date:</strong>
            <span class="setting-config">{{ renderDate }}</span></span
          >
          <span style="margin-left: 20px"
            ><strong>Event Location postcode:</strong>
            <span class="setting-config">{{ renderPostCode }}</span></span
          >
        </div>
        <div class="setting-right">
          <el-button
            type="primary"
            icon="el-icon-setting"
            @click="changeSetting"
            size="mini"
            >Setting</el-button
          >
        </div>
      </div>
      <div class="woo-planner-content">
        <woo-board
          ref="WooBoard"
          :productList="selectedProducts"
          @removeProduct="removeProduct"
          :asset="sceneAsset"
        />
      </div>
      <div class="woo-planner-side">
        <div class="side-top">
          <el-button
            class="woo-side-button"
            @click="openProductDrawer"
            type="text"
            icon="el-icon-circle-plus-outline"
          ></el-button>
          <el-button
            class="woo-side-button"
            type="text"
            icon="el-icon-picture-outline"
            @click="openSceneCart()"
          ></el-button>
          <el-button
            class="woo-side-button"
            type="text"
            icon="el-icon-s-order"
            @click="openPreset"
          ></el-button>
          <el-button
            class="woo-side-button"
            type="text"
            icon="el-icon-goods"
            @click="openCart('Check Cart')"
          ></el-button>
        </div>
        <div class="side-end">
          <el-button
            class="woo-side-button"
            type="text"
            icon="el-icon-s-claim"
            @click="saveCreation"
          ></el-button>
        </div>
      </div>
      <woo-selector
        ref="drawerSelector"
        @addProduct="addProduct"
        :eventSetting="eventSetting"
      />
      <woo-cart
        ref="drawerCart"
        :products="selectedProducts"
        :eventSetting="eventSetting"
      />
      <woo-cart-scene ref="drawerScene" @change="changeScene" />
      <woo-preset ref="drawerPreset" />
      <enter-form ref="enterForm" @update="updateEventSetting" />
    </div>
  </div>
</template>

<script>
import WooSelector from "./WooSelector.vue";
import WooBoard from "./WooBoard.vue";
import WooCart from "./WooCart.vue";
import WooCartScene from "./WooCartScene.vue";
import WooPreset from "./WooPreset.vue";
import { getOptions } from "@/api/woocommerce";
import EnterForm from "./enterForm.vue";
import moment from "moment";

export default {
  name: "WooPlanner",
  components: {
    WooSelector,
    WooBoard,
    WooCart,
    WooCartScene,
    WooPreset,
    EnterForm,
  },
  data() {
    return {
      message: "hihi",
      productDrawer: false,
      key: "ck_010d37b7192f56226e37c06d0edba3e0d4a36255",
      secret: "cs_55953fa7c708a5a41c749646d15a6650ed6f8f48",
      selectedProducts: [],
      sceneAsset: null,
      config: {},
      eventSetting: {},
    };
  },
  computed: {
    renderDate() {
      if (
        this.eventSetting &&
        this.eventSetting.date &&
        this.eventSetting.date.length > 0
      ) {
        const [start, end] = this.eventSetting.date;
        const startStr = moment(start).format("YYYY-MM-DD");
        const endStr = moment(end).format("YYYY-MM-DD");
        return `${startStr} - ${endStr}`;
      } else {
        return "unset";
      }
    },
    renderPostCode() {
      if (this.eventSetting && this.eventSetting.postcode) {
        return this.eventSetting.postcode;
      } else {
        return "unset";
      }
    },
  },
  created() {},
  mounted() {
    this.initData();
    this.$eventBus.$on("loadPreset", (preset) => {
      this.loadPreset(preset);
    });
  },
  methods: {
    loadPreset(preset) {
      try {
        const config = JSON.parse(preset.text);
        if (config && config.products && config.products.length > 0) {
          this.selectedProducts.splice(0);
          config.products.forEach((element) => {
            this.selectedProducts.push(element);
          });
          console.log("config", config);
          this.sceneAsset = {
            backgroundUrl: config.background,
          };
        }
      } catch (e) {
        this.$message.error("There is some error in the preset you check.");
      }
    },

    initData() {
      getOptions().then((res) => {
        const config = JSON.parse(res);
        this.config = config;
        if (config.images) {
          this.sceneAsset = config.images[0];
        }
      });
      const settingStr = sessionStorage.getItem("eventSetting");
      if (settingStr) {
        this.eventSetting = JSON.parse(settingStr);
      } else {
        this.openSetting();
      }
    },
    openSetting() {
      this.$refs.enterForm.open(this.eventSetting).then((data) => {
        this.updateEventSetting(data);
      });
    },
    guid() {
      return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
        /[xy]/g,
        function (c) {
          var r = (Math.random() * 16) | 0,
            v = c == "x" ? r : (r & 0x3) | 0x8;
          return v.toString(16);
        }
      );
    },
    changeSetting() {
      this.openSetting();
    },
    openProductDrawer() {
      if (!this.eventSetting.date) {
        this.openSetting();
      } else {
        this.$refs.drawerSelector.openCategory(this.config);
      }
    },
    updateEventSetting(data) {
      this.eventSetting = data;
      sessionStorage.setItem("eventSetting", JSON.stringify(data));
    },
    addProduct(product) {
      const uuid = this.guid();
      const formatProduct = {
        key: uuid,
        ...product,
      };
      this.selectedProducts.push(formatProduct);
    },
    openCart(title) {
      this.$refs.drawerCart.open(title);
    },
    openSceneCart() {
      this.$refs.drawerScene.open("Choose Scene");
    },
    changeScene(asset) {
      this.sceneAsset = asset;
    },
    openPreset() {
      const config = this.selectedProducts;
      this.$refs.drawerPreset.open("Preset", config);
    },
    removeProduct(uniqueId) {
      this.selectedProducts = this.selectedProducts.filter(
        (product) => product.key !== uniqueId
      );
    },
    saveCreation() {
      this.$eventBus.$emit("save");
    },
  },
};
</script>

<style lang="scss">
#woo-planner {
  width: 100%;
  height: 100%;
  background-color: #fff;
  box-shadow: 1px 1px 4px 1px #b5b5b580;
  .v-modal {
    position: absolute;
  }
}
.woo-setting-info {
  padding: 5px 20px;
  position: absolute;
  top: 0;
  width: calc(100% - 64px);
  z-index: 89;
  background-color: #00000078;
  color: #fff;
  i {
    cursor: pointer;
  }
  .setting-detail {
    display: inline-block;
  }
  .setting-right {
    float: right;
  }
  .setting-config {
    color: #f9c349;
  }
}

.woo-planner-container {
  position: relative;
  height: 100%;
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 64px;
}

.woo-planner-container .el-drawer__wrapper {
  position: absolute;
}

.woo-planner-content {
}

.woo-planner-side {
  width: 64px;
  background-color: #000;
  transform: none;
  height: 100%;
  display: flex;
  flex-direction: column;
  z-index: 90;
  position: relative;
  justify-content: space-between;
  .side-top {
    display: flex;
    flex-direction: column;
  }
  .side-end {
    display: flex;
    flex-direction: column;
  }
}

.woo-planner-side .woo-side-button {
  color: #fff;
  width: 100%;
  font-size: 20px;
}

.woo-planner-side .woo-side-button + .woo-side-button {
  margin: 0;
}
</style>