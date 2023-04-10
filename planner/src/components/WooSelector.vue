<template>
  <el-drawer
    class="woo-selector"
    title="Choose Category"
    :append-to-body="false"
    :visible.sync="categoryDrawerVisible"
    direction="rtl"
    :modal-append-to-body="false"
    :modal="false"
    :size="360"
  >
    <div class="woo-categories">
      <div class="woo-categories-container">
        <div
          class="woo-category-item"
          v-for="category of categories"
          :key="category.term_id"
          @click="loadCategory(category.term_id)"
        >
          <i class="el-icon-menu"></i>
          <span
            slot="title"
            style="margin-left: 15px"
            v-html="category.name"
          ></span>
        </div>
      </div>
    </div>
    <el-drawer
      title="Choose Product"
      :append-to-body="false"
      class="woo-product-drawer"
      :before-close="closeProductDrawer"
      :modal="false"
      :modal-append-to-body="false"
      direction="rtl"
      :size="360"
      :visible.sync="productDrawerVisible"
    >
      <div class="woo-products" v-loading="productLoading">
        <div class="woo-products-container" v-if="productList.length > 0">
          <el-card
            class="woo-product-line-item"
            v-for="(product, index) of productList"
            :key="`${product.id}-${index}`"
          >
            <el-image
              v-if="product.images && product.images.length > 0"
              :src="product.images[0]"
              :previewSrcList="product.images"
            ></el-image>
            <el-image v-else :src="product.feature_image"> </el-image>
            <div class="woo-cart-info">
              <div class="woo-cart-title" :title="product.name">
                {{ product.name }}
              </div>
              <div class="woo-cart-price">
                Rent from
                <span class="price-span">${{ product.min_price }}</span>
              </div>
              <el-tag
                v-show="product.variations.length > 0"
                size="mini"
                style="float: left"
                type="warning"
                >Options Available</el-tag
              >
              <div class="woo-cart-btn">
                <el-button
                  class="unavailableBtn"
                  size="small"
                  plain
                  disabled
                  v-if="
                    product.rent_available !== 'available' &&
                    product.rent_available !== 'unavailable_variable'
                  "
                  >Not Available</el-button
                >
                <el-button
                  v-else
                  class="addBtn"
                  size="small"
                  @click="addProduct(product)"
                  >Add</el-button
                >
              </div>
            </div>
          </el-card>
        </div>
      </div>
      <div class="woo-products-footer">
        <el-button
          @click="closeProductDrawer"
          size="small"
          type="primary"
          icon="el-icon-d-arrow-left"
          >Return</el-button
        >
      </div>
    </el-drawer>
    <variation-product ref="productDialog" />
  </el-drawer>
</template>

<script>
/* eslint-disable */
import moment from "moment";

import {
  getProducts,
  getOptions,
  getWooCategoryByIds,
} from "@/api/woocommerce";
import placeholder from "@/assets/placeholder.png";
import VariationProduct from "./VariationProduct.vue";
export default {
  components: { VariationProduct },
  name: "WooSelector",
  props: {
    msg: String,
    eventSetting: {
      type: Object,
      default: () => {},
    },
  },
  data() {
    return {
      categoryDrawerVisible: false,
      productDrawerVisible: false,
      categories: [], // 提取woocommerce产品分类
      productList: [],
      placeholderUrl: placeholder,
      productLoading: false,
      currentId: null,
    };
  },
  watch: {
    eventSetting() {
      if (this.currentId) {
        this.loadCategory(this.currentId);
      }
    },
  },
  methods: {
    handleClose(done) {
      this.$confirm("Confirm close?", "Alert", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        // type: "info",
      })
        .then(() => {
          this.closeProductDrawer();
          done();
        })
        .catch(() => {});
    },
    closeAll() {
      this.categoryDrawerVisible = false;
      this.categories = [];
      this.closeProductDrawer();
    },
    closeProductDrawer() {
      this.productList = [];
      this.productDrawerVisible = false;
    },
    getOptionCategories() {
      return new Promise((resolve, reject) => {
        getOptions()
          .then((res) => {
            const data = JSON.parse(res);
            resolve(data.categories);
          })
          .catch((e) => {
            reject(e);
          });
      });
    },
    async openCategory(config) {
      this.categoryDrawerVisible = true;
      // const categories = await this.getOptionCategories();
      const categories = config.categories || [];
      const ids = categories.map((item) => item[item.length - 1]);
      const params = {
        ids: ids,
      };
      getWooCategoryByIds(params)
        .then((res) => {
          this.categories = res;
        })
        .catch(() => {
          this.categories = [];
        });

      // const options = {
      //   orderby: "name",
      //   parent,
      //   per_page: 100,
      // };
      // getCategories(options)
      //   .then((res) => {
      //     let resArr = [...res];
      //     resArr.sort((a, b) => a.menu_order - b.menu_order);
      //     this.categories = resArr;
      //   })
      //   .catch(() => {
      //     this.categories = [];
      //   });
    },
    loadCategory(id) {
      this.currentId = id;
      this.productLoading = true;
      this.productDrawerVisible = true;
      const setting = {
        fromDate: this.eventSetting.date[0],
        endDate: this.eventSetting.date[1],
        postcode: this.eventSetting.postcode,
      };
      getProducts(id, setting)
        .then((res) => {
          this.productList = res;
        })
        .finally(() => {
          this.productLoading = false;
        });
    },
    addProduct(product) {
      if (product.variations && product.variations.length > 0) {
        this.$refs.productDialog.open(product).then((data) => {
          this.confirmProduct(data);
        });
      } else {
        this.confirmProduct(product);
      }
    },
    confirmProduct(product) {
      console.log("product", product);
      this.$emit("addProduct", product);
      this.closeAll();
    },
  },
};
</script>

<style lang="scss">
.woo-selector {
  .woo-product-drawer {
    .el-drawer {
      background-color: #fff;
    }
  }
  .el-drawer__header {
    margin-bottom: 0;
  }
  .woo-categories {
    padding: 20px 0 55px 0;
  }
  .woo-products {
    height: 100%;
    padding: 20px 0 55px 0;
  }
  .woo-products-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 10px;
    justify-items: center;
    align-items: flex-start;
    padding: 0 20px 0 20px;
  }
  .woo-categories-container {
    display: block;
    padding: 0 20px 0 20px;
  }

  .woo-products-container {
    height: 100%;
    overflow: scroll;
    padding: 0 20px 0 20px;
    .woo-product-line-item {
      margin-bottom: 15px;
    }
    .woo-product-line-item .el-card__body {
      position: relative;
      display: flex;
      gap: 10px;
      .woo-cart-info {
        flex-grow: 1;
        position: relative;
        .woo-cart-title {
          width: 200px;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          font-size: 14px;
          font-weight: 600;
        }
        .woo-cart-price {
          font-size: 12px;
        }
      }
      .woo-cart-btn {
        .addBtn {
          padding: 5px 10px;
          background-color: #f7d900;
          color: #fff;
          border: none;
          float: right;
        }
        .unavailableBtn {
          text-transform: unset;
          padding: 5px 10px;
          float: right;
        }
      }

      .el-image {
        flex-grow: 0;
        width: auto;
        height: 100%;
        border: 1px solid #e3e3e3;
      }
    }
  }

  .woo-products-wrapper {
    height: 100%;
    overflow: scroll;
  }
  .woo-category-item,
  .woo-product-item {
    width: 100%;
    border: 1px solid #aaaaaa2b;
    cursor: pointer;
    &:hover {
      box-shadow: 1px 1px 3px 1px #b6b6b645;
    }
    .woo-category-image,
    .woo-product-image {
      line-height: 0;
      height: 160px;
      display: flex;
      align-items: center;
      overflow: hidden;
    }
  }
  .woo-category-item {
    padding: 10px;
    background-color: #f9c349;
    color: #fff;
    font-size: 14px;
    border-radius: 15px;
  }
  .woo-category-item + .woo-category-item {
    margin-top: 10px;
  }
  .woo-category-info {
    text-align: center;
    font-size: 14px;
    line-height: 1.2em;
    height: 4em;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f4f4f4;
  }
  .woo-product-info {
    text-align: center;
    line-height: 1.2em;
    height: 4em;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    background-color: #f4f4f4;
    .woo-product-title {
      font-size: 14px;
      text-overflow: ellipsis;
      line-clamp: 2;
      overflow: hidden;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      display: -webkit-box;
    }
  }
  .woo-product-drawer .el-drawer__body {
    position: relative;
  }
  .woo-products-footer {
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
  }
}
</style>
