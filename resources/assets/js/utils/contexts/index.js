import React from 'react';
import { singlePostInitial, userInfoInitial } from './initialStates';

export const CategoryContext = React.createContext({});
export const GroupContext = React.createContext({});
export const UsersContext = React.createContext({
  users: {},
  updateUsers: () => {},
});
export const UpdateCategoryContext = React.createContext({
  updateCategory: () => {},
  categoryId: null,
});
export const UpdateGroupContext = React.createContext({
  updateGroup: () => {},
  groupId: null,
  groupMembers: [],
});

export const PostsFeedContext = React.createContext({
  postData: {
    posts: {},
    offset: 0,
    space_groups: {},
  },
  hasMoreFeeds: true,
  fetchPosts: () => {},
  updatePosts: () => {},
});

export const BusinessReviewContext = React.createContext({
  reviewLoadData: {
    reviews: [],
    offset: 0,
    loaded: false,
  },
  hasMoreReviews: true,
  fetchReviews: () => {},
  updateReviews: () => {},
});

export const SinglePostContext = React.createContext(singlePostInitial);

export const UserInfoContext = React.createContext(userInfoInitial);
