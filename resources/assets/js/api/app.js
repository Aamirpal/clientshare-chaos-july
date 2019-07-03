import axios from 'axios';
import { API_URL, globalConstants } from '../utils/constants';

const { clientShareId, userId } = globalConstants;

export const getCommunityMember = id => axios.get(`${API_URL}/community_member_tile/${id}`);

export const getCommunitySpaceInfo = spaceId => axios.get(`${API_URL}/community-space-info/${spaceId}`);

export const getCommunityMembers = (spaceId, company_id = null, limit = null, offset = null) => {
  const query = {
    company_id,
    limit,
    offset,
  };
  return axios.get(`${API_URL}/community-members/${spaceId}`, { params: query });
};

export const getSpaceCategories = spaceId => axios.get(`${API_URL}/space-categories/${spaceId}`);

export const getAllUsers = () => axios.get(`${API_URL}/get-space-users/${clientShareId}`);

export const getNotifications = (spaceId, userid, offset) => axios.get(`${API_URL}/get-share-notifications/${spaceId}/${userid}/${offset}`);

export const getSearchResult = keyword => axios.get(`${API_URL}/global-search/${keyword}/${clientShareId}/${userId}/2`);

export const getShareMembers = () => axios.get(`${API_URL}/search-space-users/${clientShareId}`);

export const getGroupList = () => axios.get(`${API_URL}/get-user-groups/${clientShareId}/${userId}`);

export const groupCreate = group => axios.post(`${API_URL}/group-create`, {
  ...group,
  space_id: clientShareId,
});
export const groupUpdate = group => axios.post(`${API_URL}/group-update`, {
  ...group,
  space_id: clientShareId,
});
export const groupDelete = groupId => axios.delete(`${API_URL}/group-delete/${groupId}`);

export const getGroupMembers = groupId => axios.get(`${API_URL}/group-members/${clientShareId}/${groupId}`);

export const addPostApi = post => axios.post(`${API_URL}/post`, {
  ...post,
  user_id: userId,
  space_id: clientShareId,
});

export const editPostApi = (postId, post) => axios.patch(`${API_URL}/post/${postId}`, {
  ...post,
  user_id: userId,
  space_id: clientShareId,
});

export const deleteGroupMembers = groupUserId => axios.delete(`${API_URL}/delete-groups-member/${groupUserId}`);
export const getS3Token = () => axios.get(`${API_URL}/aws-access`);

export const deleteAttachment = file => axios.post(`${API_URL}/remove-file`, file);

export const getUrlData = url => axios.get(`${API_URL}/get-url-data`, {
  params: { q: url },
});

/** Feed Api */
export const getFeedPosts = (params, source) => axios.get(`${API_URL}/get-posts`, {
  params: {
    space_id: clientShareId,
    ...params,
  },
  cancelToken: source.token,
});

export const endorsePost = (post_id, endorse) => axios.post(`${API_URL}/endorse-post`,
  {
    post_id,
    endorse,
  });

export const getEndroseUsers = postId => axios.get(`${API_URL}/get-endorse-users/${clientShareId}/${postId}`);

export const addView = postId => axios.post(`${API_URL}/post-view`, {
  post_id: postId,
  space_id: clientShareId,
  user_id: userId,
});

export const addComment = params => axios.post(`${API_URL}/add-comment`, {
  space_id: clientShareId,
  user_id: userId,
  ...params,
});

export const editCommentApi = (params, commentId) => axios.patch(`${API_URL}/update-comment/${commentId}`, {
  space_id: clientShareId,
  user_id: userId,
  ...params,
});

export const deleteCommentApi = commentId => axios.delete(`${API_URL}/delete-comment/${commentId}`);

export const getSinglePost = postId => axios.get(`${API_URL}/get-post/${clientShareId}/${postId}`);

export const getUrlPreviewApi = params => axios.post(`${API_URL}/get-viewer`, params);
export const addReviewApi = postReview => axios.post(`${API_URL}/create-business-review`, {
  ...postReview,
  user_id: userId,
  space_id: clientShareId,
});

export const editReviewApi = (postReview, id) => axios.patch(`${API_URL}/business-review/${id}`, {
  ...postReview,
  user_id: userId,
  space_id: clientShareId,
});

export const reviewList = offset => axios.get(`${API_URL}/list-business-reviews/${userId}/${clientShareId}/${offset}`);

export const pinPost = (post_id, status) => axios.get(`${API_URL}/pin-post/${clientShareId}/${post_id}/${status}`);

export const deletePost = postId => axios.delete(`${API_URL}/post/${postId}`);
export const saveTwitterHandler = handlers => axios.post(`${API_URL}/save-twitter-handler`, {
  twitter_handles: handlers,
  user_id: userId,
  space_id: clientShareId,
});

export const getTwitterHandler = () => axios.get(`${API_URL}/get-twitter-handler/${clientShareId}`);
export const getReviewPost = businessReview => axios.get(`${API_URL}/business-review/${businessReview}`);

export const deleteAttendee = (businessReviewId, spaceUserId) => axios.delete(`${API_URL}/delete-attendees/${businessReviewId}/${spaceUserId}`);
export const deleteBusinessReview = businessReviewId => axios.delete(`${API_URL}/business-review/${businessReviewId}`);
export const shareProfileProgress = () => axios.get(`${API_URL}/share-profile-status`, {
  params: {
    space_id: clientShareId,
  },
});

export const getUserInfo = user_id => axios.get(`${API_URL}/user_information/${clientShareId}/${user_id}`);
