import { uploadImage } from "@/api/woocommerce";

export function dataURItoBlob(dataURI) {
  const byteString = atob(dataURI.split(',')[1]);
  const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
  const ab = new ArrayBuffer(byteString.length);
  const ia = new Uint8Array(ab);
  for (let i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }
  return new Blob([ab], { type: mimeString });
}


export async function uploadFile(file, type = 'png') {
  const formData = new FormData();
  const fileName = `${new Date().getTime()}.${type}`;
  formData.append("file", file, fileName);
  const response = await uploadImage(formData);
  // Return uploaded image URL
  return response.url;
}