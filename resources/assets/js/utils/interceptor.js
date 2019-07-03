import axios from 'axios';

axios.interceptors.response.use((response) => {
  const { data } = response;
  return Promise.resolve(data);
}, error => Promise.reject(error.response.data));
