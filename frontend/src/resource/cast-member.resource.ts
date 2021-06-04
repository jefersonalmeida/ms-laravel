import ApiResource from './api.resource';
import {resourceVideo} from './index';

const castMemberResource = new ApiResource(resourceVideo, 'cast-members');
export default castMemberResource;
