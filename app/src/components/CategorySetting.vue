<template>
  <div class="category-setting">
    <el-cascader
      :options="options"
      v-model="selected"
      :show-all-levels="false"
      placeholder="Select Categories"
      :props="{
        multiple: true,
        checkStrictly: true,
        label: 'name',
        value: 'id',
      }"
      clearable
    ></el-cascader>
  </div>
</template>

<script>
import { getProductCategories } from "@/utils/api";
export default {
  name: "categorySetting",
  data() {
    return {
      options: [],
      selected: [],
    };
  },
  created() {
    this.initCategories();
  },
  methods: {
    updateSelected(value){
      console.log(value);
      this.selected = value
    },
    getParams() {
      return this.selected
    },
    initCategories() {
      getProductCategories().then((res) => {
        this.options = res.data;
      });
    },
  },
};
</script>

<style>
</style>