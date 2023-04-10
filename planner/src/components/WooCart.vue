<template>
  <el-drawer
    class="woo-cart-drawer"
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
          v-for="(product, index) of products"
          :key="`${product.id}-${index}`"
        >
          <el-image :src="imgSrc(product)"></el-image>
          <div class="woo-cart-info">
            <div class="woo-cart-title">{{ product.name }}</div>
            <div class="woo-cart-tag" v-if="product.selectVariation">
              <el-tag size="mini" type="warning">{{
                variationName(product.selectVariation)
              }}</el-tag>
            </div>
            <div class="woo-cart-price">${{ rentalPrice(product) }}/per day</div>
          </div>
        </el-card>
      </div>
      <div class="woo-cart-footer">
        <el-button @click="addMultipleToCart" size="small" type="success"
          >Submit</el-button
        >
        <el-button @click="closeCartDrawer" size="small" type="danger"
          >Close</el-button
        >
      </div>
    </div>
  </el-drawer>
</template>

<script>
/* eslint-disable */
import placeholder from "@/assets/placeholder.png";
import { addToCart } from "@/api/woocommerce";
export default {
  props: {
    products: Array,
    eventSetting: Object,
  },
  name: "WooCart",
  data() {
    return {
      cartVisible: false,
      drawerTitle: "Cart",
      placeholderUrl: placeholder,
    };
  },
  methods: {
    imgSrc(product) {
      if (product.selectVariation) {
        return product.selectVariation.image.full_src;
      } else {
        return product.images.length > 0 ? product.images[0]: product.feature_image;
      }
    },
    rentalPrice(product) {
      if (product.selectVariation) {
        if (product.rental_purchase) {
          return product.selectVariation
            .wcrp_rental_products_rental_purchase_price;
        } else {
          return product.selectVariation.display_price;
        }
      } else {
        return product.min_price;
      }
    },
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
    open(title) {
      this.drawerTitle = title;
      this.cartVisible = true;
    },
    closeCartDrawer() {
      this.cartVisible = false;
    },
    addMultipleToCart(isRental = true) {
      if ("undefined" === typeof wc_add_to_cart_params) {
        // The add to cart params are not present.
        return false;
      }
      const products = this.products.map((product) => {
        const resObj = {
          product_id: product.id,
          quantity: 1,
        };
        if (product.selectVariation) {
          resObj.variation_id = product.selectVariation.variation_id;
        }
        if (isRental) {
          resObj.wcrp_rental_products_rental_form_nonce =
            kongfuseo_addon_data.nonce;
          resObj.wcrp_rental_products_rent_from = this.eventSetting.date[0];
          resObj.wcrp_rental_products_rent_to = this.eventSetting.date[1];
        }
        return resObj;
      });
      jQuery.post(
        wc_add_to_cart_params.ajax_url, // WooCommerce AJAX endpoint
        {
          action: "add_multiple_to_cart",
          products: products,
        },
        function (response) {
          // Handle the response
          // This is important so your theme gets a chance to update the cart quantity for example, but can be removed if not needed.
          jQuery(document.body).trigger("added_to_cart", [
            response.fragments,
            response.cart_hash,
          ]);

          window.location.href = `${window.location.origin}/cart/`;
        }
      );
    },
    addCart() {
      const ids = this.products.map((item) => item.id);

      const config = this.products.map((product) => {
        const productInfo = {
          product_id: product.id,
          quantity: 1,
        };
        if (product.selectVariation) {
          productInfo.variation_id = product.selectVariation.variation_id;
        }
        return productInfo;
      });
      // addToCart(config).then((res) => {
      //   console.log(res);
      // });
      // if(ids.length>0){
      //   ids.forEach(id => {
      //       this.woocommerceAddToCart(id);
      //   });
      // }
      if (ids.length > 0) {
        window.location.href = `${window.origin}/cart/?add-to-cart=${ids.join(
          ","
        )}`;
      }
    },
    woocommerceAddToCart(product_id) {
      if ("undefined" === typeof wc_add_to_cart_params) {
        // The add to cart params are not present.
        return false;
      }

      var data = {
        product_id: product_id,
        quantity: 1,
      };

      jQuery.post(
        wc_add_to_cart_params.wc_ajax_url
          .toString()
          .replace("%%endpoint%%", "add_to_cart"),
        data,
        function (response) {
          if (!response) {
            return;
          }

          // This redirects the user to the product url if for example options are needed ( in a variable product ).
          // You can remove this if it's not the case.
          if (response.error && response.product_url) {
            window.location = response.product_url;
            return;
          }

          // Remove this if you never want this action redirect.
          if (wc_add_to_cart_params.cart_redirect_after_add === "yes") {
            window.location = wc_add_to_cart_params.cart_url;
            return;
          }

          // This is important so your theme gets a chance to update the cart quantity for example, but can be removed if not needed.
          jQuery(document.body).trigger("added_to_cart", [
            response.fragments,
            response.cart_hash,
          ]);
        }
      );
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