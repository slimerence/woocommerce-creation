<template>
  <div class="theme-setting">
    <mtc-wordpress-media-library multiple edit v-model="images">
      <template #add-button-content>Select Images</template>
    </mtc-wordpress-media-library>
  </div>
</template>

<script>
import MtcWordpressMediaLibrary from "@mtcmedia/vue-wordpress-media-library";

export default {
  components: {
    MtcWordpressMediaLibrary,
  },
  name: "ThemeSetting",
  props: {
    optionImages: Array,
  },
  data() {
    return {
      images: [],
    };
  },
  watch: {
    optionImages: {
      deep: true,
      immediate: true,
      handler() {
        this.images = this.optionImages;
      },
    },
  },
  methods: {
    getParams() {
      return {
        images: this.images,
      };
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
.mtc_wpml__grid-container {
  display: flex;
  gap: 20px;
}
.theme-setting /deep/ .mtc_wpml__grid-container {
  display: flex;
}
.theme-setting /deep/ .mtc_wpml__grid-item {

}
.mtc_wpml__thumbnail-image {
  line-height: 0;
}
.mtc_wpml__button {
  display: inline-block;
  border: none;
  padding: 10px;
  width: 100%;
  cursor: pointer;
}
.mtc_wpml__button--remove {
  background-color: #ff6464;
}
.mtc_wpml__button--remove .mtc_wpml__button-text {
  color: #fff;
}
.mtc_wpml__button--remove .mtc_wpml__button-icon{
  margin-left: 15px;
  color: #fff;
}
.mtc_wpml__grid-item.mtc_wpml__grid-item--edit-button{
  display: none;
}
</style>
