import request from '@/utils/request'

const baseURL = '';

export function getCategories(params) {
  return request({
    baseURL,
    url: '/wp-json/wc/v3/products/categories',
    method: 'get',
    params
  })
}

export function getProducts(id, data) {
  return request({
    baseURL,
    // url: '/wp-json/wc/v3/products',
    url: `/wp-json/kongfuseo-admin-setting-panel/products/${id}`,
    method: 'post',
    // params
    data
  })
}

export function getOptions() {
  return request({
    baseURL,
    url: '/wp-json/kongfuseo-admin-setting-panel/get',
    method: 'get',
  })
}

export function getPresets() {
  return request({
    baseURL,
    url: '/wp-json/kongfuseo-admin-setting-panel/preset/fetch',
    method: 'post',
  })
}

export function savePresets(data) {
  return request({
    baseURL,
    url: '/wp-json/kongfuseo-admin-setting-panel/preset/post',
    method: 'post',
    data
  })
}

export function getWooCategoryByIds(data) {
  return request({
    baseURL,
    url: '/wp-json/kongfuseo-admin-setting-panel/product-category/fetch',
    method: 'post',
    data
  })
}

export function addToCart(data) {
  return request({
    baseURL,
    url: '/wp-json/kongfuseo/v1/add-to-cart',
    method: 'post',
    data
  })
}


export function uploadImage(data) {
  return request({
    baseURL,
    url: '/wp-json/kongfuseo-admin-setting-panel/image-upload',
    method: 'post',
    headers: {
      "Content-Type": "multipart/form-data",
    },
    data
  })
}