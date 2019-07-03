import axios from 'axios';
import uuid from 'uuid';
import { xmlToJson, getType, getFileExtension } from '../utils/methods';

export const fileUpload = (
  acceptedFile, token, progress, source,
) => new Promise(((resolve, reject) => {
  
  const file = acceptedFile;
  const ext = getFileExtension(acceptedFile.name);
  const data = token.inputs;
  const formData = new FormData();
 
  const filename = `postfile/${uuid.v4()}.${ext}`;
  formData.append('key', filename);
  Object.keys(data).map(key => formData.append(key, data[key]));

  formData.append('file', file);
  return axios.post(token.url, formData, {
    onUploadProgress(progressEvent) {
      const response = {
        progress: Number(Math.round((progressEvent.loaded * 100) / progressEvent.total)),
      };
      progress(response);
    },
    cancelToken: source.token,
  }).then((res) => {
    const parser = new DOMParser();
    const response = xmlToJson(parser.parseFromString(res, 'text/xml'));
    const type = getType(ext);
    response.type = type;
    response.file = file;
    return resolve(response);
  }).catch(err => reject(err));
}));
