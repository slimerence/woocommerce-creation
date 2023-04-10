<template>
  <div id="app-kongfuseo">
    <h1 class="wp-heading-inline">Our Creations Settings</h1>
    <el-tabs
      class="setting-main"
      v-model="activeName"
      @tab-click="handleClick"
      type="border-card"
    >
      <el-tab-pane label="Theme" name="theme">
        <h2 class="theme-title">Creation Scene Setting</h2>
        <theme-setting
          ref="themeSetting"
          :optionImages="images"
          @onUpdate="updateImages"
        />
        <h2 class="theme-title">Creation Category Setting</h2>
        <category-setting ref="categorySetting" />
      </el-tab-pane>
    </el-tabs>
    <el-button type="primary" @click="handleSave">Save</el-button>
  </div>
</template>

<script>
/* eslint-disable */
import ThemeSetting from "./components/ThemeSetting.vue";
import { saveOptions } from "@/utils/api";
import CategorySetting from "./components/CategorySetting.vue";
export default {
  name: "App",
  components: {
    ThemeSetting,
    CategorySetting,
  },
  data() {
    return {
      activeName: "theme",
      images: [],
    };
  },
  watch: {},
  created() {
    // eslint-disable-next-line
    if (vue_wp_settings_data && vue_wp_settings_data.images) {
      this.images = vue_wp_settings_data.images;
    }
  },
  mounted() {
    if (vue_wp_settings_data && vue_wp_settings_data.images) {
      this.images = vue_wp_settings_data.images;
    }
    console.log(vue_wp_settings_data);
    if (vue_wp_settings_data && vue_wp_settings_data.categories) {
      this.$refs.categorySetting.updateSelected(
        vue_wp_settings_data.categories
      );
    }
  },
  methods: {
    handleClick(tab, event) {
      console.log(tab, event);
    },
    updateImages(newImages) {
      console.log("newImages", newImages);
    },
    generateParams() {
      const themeOptions = this.$refs.themeSetting.getParams();
      const categoryOptions = this.$refs.categorySetting.getParams();
      return { ...themeOptions, ...categoryOptions };
    },
    handleSave() {
      const params = this.generateParams();
      console.log(params);
      // return;
      // const postString = JSON.stringify(params);
      // console.log("save", params, postString);
      saveOptions(params)
        .then((res) => {
          console.log("res", res);
          if (res) {
            this.$message.success("update success");
          } else {
            this.$message.error("server error");
          }
        })
        .catch(() => {
          this.$message.error("server error");
        });
    },
  },
};
</script>

<style>
#app-kongfuseo {
  margin-top: 30px;
  padding-right: 20px;
}
.setting-main {
  margin-bottom: 20px;
}
.theme-title {
  line-height: 1.5em;
  border-bottom: 1px solid #e3e3e3;
}
</style>
