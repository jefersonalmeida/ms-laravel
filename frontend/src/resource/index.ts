import axios from 'axios';

export const resourceVideo = axios.create({
  baseURL: process.env.REACT_APP_MICRO_VIDEO_ENDPOINT_API,
});
