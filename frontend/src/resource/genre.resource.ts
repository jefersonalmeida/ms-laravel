import ApiResource from './api.resource';
import {resourceVideo} from './index';

const genreResource = new ApiResource(resourceVideo, 'genres');
export default genreResource;
