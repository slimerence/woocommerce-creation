<template>
  <el-dialog
    class="productDialog"
    title="Product Detail"
    :visible.sync="dialogVisible"
    :modal="false"
    width="800px"
  >
    <div class="product-dialog-wrapper">
      <el-row :gutter="20">
        <el-col :span="8">
          <div class="product-dialog-left">
            <el-image class="product-dialog-image" :src="currentImg"></el-image>
          </div>
        </el-col>
        <el-col :span="16">
          <div class="product-dialog-right">
            <h2>{{ productInfo.name }}</h2>
            <p>Rent Price From: ${{ rentalPrice }}</p>
            <div class="product-variations-wrapper">
              <el-tag
                v-for="variation of productInfo.variations"
                :key="variation.variation_id"
                :class="{
                  disabled:
                    variation.rent_available.indexOf('unavailable_') > -1,
                }"
                @click="chooseVariation(variation)"
              >
                {{ variationName(variation) }}
              </el-tag>
            </div>
            <div class="buttons">
              <el-button
                type="primary"
                size="small"
                :disabled="disableAdd"
                @click="addProduct"
                >Add Product</el-button
              >
            </div>
          </div>
        </el-col>
      </el-row>
    </div>
  </el-dialog>
</template>

<script>
export default {
  name: "productDialog",
  data() {
    return {
      productInfo: {},
      dialogVisible: false,
      currentImg: "",
      currentVariation: null,
      resolve: null,
      reject: () => false,
    };
  },
  mounted() {},
  computed: {
    rentalPrice() {
      if (this.currentVariation) {
        if (this.productInfo.rental_purchase) {
          return this.currentVariation
            .wcrp_rental_products_rental_purchase_price;
        } else {
          return this.currentVariation.display_price;
        }
      } else {
        return this.productInfo.min_price;
      }
    },
    disableAdd() {
      return !this.currentVariation;
    },
    isAdmin() {
      // eslint-disable-next-line no-undef
      return !!vue_isadmin;
    },
  },
  methods: {
    variationName(variation) {
      if (variation.attributes) {
        let name = "";
        for (let key in variation.attributes) {
          if (name === "") {
            name += variation.attributes[key];
          } else {
            name += `+${variation.attributes[key]}`;
          }
        }
        return name;
      } else {
        return "";
      }
    },
    chooseVariation(variation) {
      if(variation.rent_available.indexOf('unavailable_') > -1 && !this.isAdmin){
        return false;
      }
      this.currentImg = variation.image.full_src;
      this.currentVariation = variation;
    },
    open(product) {
      this.productInfo = product;
      this.currentImg = this.productInfo.images[0];
      this.currentVariation = null;
      this.dialogVisible = true;
      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
      });
    },
    addProduct() {
      if (this.resolve) {
        const product = JSON.parse(JSON.stringify(this.productInfo));
        product.selectVariation = this.currentVariation;
        this.resolve(product);
        this.dialogVisible = false;
      }
    },
  },
};
</script>

<style lang="scss">
.productDialog .el-dialog__body {
  padding: 10px 20px;
}
.product-dialog-right {
  h2 {
    font-size: 20px;
  }
  .buttons {
    text-align: right;
  }
}
.product-variations-wrapper {
  .el-tag {
    color: #fff;
    border: none;
    background-color: #49c7f9;
    margin-bottom: 10px;
    cursor: pointer;
    &:hover {
      background-color: #31c2fb;
    }
    &.disabled{
      cursor: not-allowed;
      background-color: #49c7f969;
    }
  }
  .el-tag + .el-tag {
    margin-left: 10px;
  }
}
</style>