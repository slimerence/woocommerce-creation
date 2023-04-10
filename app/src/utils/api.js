/* eslint-disable no-undef */
import request from '@/utils/request'

// const baseURL = '';

export function saveOptions(data) {
  return request({
    url: vue_wp_api_url + '/save',
    method: 'post',
    data,
    // headers: {
    //   "Content-Type": "text/plain"
    // }
  })
}

export function getProductCategories(data) {
  return request({
    url: vue_wp_api_url +  '/product-category/get',
    method: 'get',
    data
  })
}