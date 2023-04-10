import crypto from 'crypto-js';
import oauth1a from 'oauth-1.0a';

const CONSUMER_KEY = "ck_0e41cabcf34244c628a61c338dc48127e44c550b";
const CONSUMER_SECRET = "cs_7d0eaf0896cefc8271a9be876c53036a8a9433d8";

class Oauth1Helper {
  static getAuthHeaderForRequest(request) {
    const oauth = oauth1a({
      consumer: {key: CONSUMER_KEY, secret: CONSUMER_SECRET},
      signature_method: 'HMAC-SHA1',
      hash_function(base_string, key) {
        return crypto.algo.HMAC
          .create(crypto.algo.SHA1, key)
          .update(base_string)
          .finalize()
          .toString(crypto.enc.Base64);
      },
    });

    const authorization = oauth.authorize(request);

    return oauth.toHeader(authorization);
  }
}

export default Oauth1Helper;