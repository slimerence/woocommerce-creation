/* eslint-disable no-undef */
import axios from 'axios'
import { Message } from 'element-ui'
// import Oauth1Helper from '@/utils/oauth1';

// create an axios instance
const service = axios.create({
  baseURL: '/', // url = base url + request url
  // withCredentials: true, // send cookies when cross-domain requests
  timeout: 50000 // request timeout
})

// request interceptor
service.interceptors.request.use(
  config => {
    // const options = {
    //   url: 'http://localhost:8080/wp-json/wc/v3/products/categories',
    //   method: 'get',
    // }
  
    config.headers["Access-Control-Allow-Origin"] = "*";
    config.headers["Accept"] = "application/json";

    // do something before request is sent
    config.headers["X-WP-Nonce"] = wpApiSettings.nonce;
    return config
  },
  error => {
    // do something with request error
    console.log(error) // for debug
    return Promise.reject(error)
  }
)

// response interceptor
service.interceptors.response.use(
  /**
   * If you want to get http information such as headers or status
   * Please return  response => response
  */

  /**
   * Determine the request status by custom code
   * Here is just an example
   * You can also judge the status by HTTP Status Code
   */
  response => {
    const res = response.data
    // if the custom code is not 20000, it is judged as an error.
    if (response.status !== 200) {
      return Promise.reject(new Error(res.message || 'Error'))
    } else {
      return res
    }
  },
  error => {
    console.log('err' + error) // for debug
    Message({
      message: error.message,
      type: 'error',
      duration: 5 * 1000
    })
    return Promise.reject(error)
  }
)

export default service