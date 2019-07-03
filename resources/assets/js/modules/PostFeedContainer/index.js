import React, { useEffect, useState, useContext } from 'react';
import injectStyle from 'react-jss';
import { toast } from 'react-toastify';
import PropTypes from 'prop-types';
import InfiniteScroll from 'react-infinite-scroller';
import { Facebook } from 'react-content-loader';
import get from 'lodash/get';
import _values from 'lodash/values';
import isEmpty from 'lodash/isEmpty';

import { globalConstants } from '../../utils/constants';
import {
  getSinglePost, deletePost, pinPost, endorsePost, addView, getUserInfo,
} from '../../api/app';
import { PostFeed, Modal, Spinner } from '../../components';
import { MemberTile } from '../../components/Tile';
import { EditPostModal, UsersModal } from '../Modals';
import { PostsFeedContext, SinglePostContext, UserInfoContext } from '../../utils/contexts';
import { singlePostInitial, userInfoInitial } from '../../utils/contexts/initialStates';
import { getWindowHeight, getPostIdfromUrl } from '../../utils/methods';
import { styles } from './styles';

const { userId, clientShareId } = globalConstants;
const PostFeedContainer = React.memo(({ classes }) => {
  const [singlePostData, setSinglePostData] = useState(singlePostInitial);
  const [userInfo, setUserInfo] = useState(userInfoInitial);
  const [editPost, setEditPost] = useState(null);
  const [users, showUsers] = useState({
    loading: false,
    data: null,
  });
  const feedContext = useContext(PostsFeedContext);
  const {
    postData: { posts }, fetchPosts, hasMoreFeeds, updatePosts,
  } = feedContext;

  useEffect(() => {
    const postUrlId = getPostIdfromUrl();
    if (postUrlId) {
      getSinglePost(postUrlId).then(({ data }) => {
        if (data) {
          setSinglePostData({
            singlePost: data,
            isShowSinglePost: true,
          });
          posts[data.id] = {
            ...data,
            single: true,
          };
          updatePosts(posts);
          window.history.replaceState({}, document.title, `/clientshare/${clientShareId}`);
        }
      }).catch(() => toast.error('Post Not Found'));
      addView(postUrlId).catch(() => false);
    }
  }, []);

  const deletePostApi = (post) => {
    deletePost(post.id).then(() => {
      fetchPosts(null, true);
      // setSinglePostData(null);
    }).catch(() => false);
  };

  const pinPostEvent = (post) => {
    const isPinned = get(post, 'pin_status', false);
    pinPost(post.id, isPinned ? 0 : 1).then(() => {
      fetchPosts(null, true);
      // setSinglePostData(null);
    }).catch(({ message }) => toast.error(get(message, 'errors')));
  };

  const setEndorsePost = (post, single = false) => {
    // Endrose Post
    const checkEndorse = get(post, 'endorse_by_me', []).length;
    const updatedMe = {
      endorse_by_me: [],
    };
    if (!checkEndorse) {
      updatedMe.endorse_by_me.push({
        post_id: post.id,
        user_id: userId,
      });
      updatedMe.endorse_count = get(post, 'endorse_count', 0) + 1;
    } else {
      updatedMe.endorse_count = get(post, 'endorse_count', 0) - 1;
    }
    posts[post.id] = {
      ...posts[post.id],
      ...updatedMe,
    };
    updatePosts(posts);
    if (single) {
      setSinglePostData(prev => ({
        ...prev,
        singlePost: {
          ...prev.singlePost,
          ...updatedMe,
        },
      }));
    }
    endorsePost(post.id, checkEndorse ? 0 : 1).catch(() => false);
  };

  const updateSinglePost = (data = {}) => {
    setSinglePostData({
      ...singlePostData,
      ...data,
    });
  };

  const updateUserInfo = (id, post) => {
    getUserInfo(id || get(post, 'user_id', null)).then(({ data }) => {
      setUserInfo({
        ...userInfo,
        info: data[0],
        isInfo: true,
      });
      updateSinglePost({
        isShowSinglePost: false,
      });
    }).catch(() => false);
  };

  const { singlePost, isShowSinglePost } = singlePostData;
  const { info, isInfo } = userInfo;
  return (
    <SinglePostContext.Provider value={{ singlePost, isShowSinglePost, updateSinglePost }}>
      <UserInfoContext.Provider value={{ updateUserInfo, info, isInfo }}>
        <InfiniteScroll
          pageStart={0}
          loadMore={fetchPosts}
          hasMore={hasMoreFeeds}
          loader={<div className={classes.postFeedContainer} key={0}><Facebook /></div>}
          threshold={getWindowHeight(2)}
        >
          <div>
            {_values(posts).map(post => (
              <div className={classes.postFeedContainer} key={get(post, 'id')}>
                <PostFeed
                  post={post}
                  onDelete={deletePostApi}
                  onPinPost={pinPostEvent}
                  onEditPost={() => setEditPost(post)}
                  onEndorsePost={() => setEndorsePost(post)}
                />
              </div>
            ))}
          </div>

        </InfiniteScroll>
        {isShowSinglePost && (
          <Modal
            modelProps={{
              show: isShowSinglePost,
              onHide: () => {
                delete posts[singlePost.id];
                updatePosts(posts);
                setSinglePostData(singlePostInitial);
              },
              dialogClassName: classes.singlePostContainer,
              className: 'single-post-modal',
            }}
            mobileClose
          >
            <PostFeed
              post={singlePost}
              onDelete={deletePostApi}
              onPinPost={pinPostEvent}
              onEditPost={() => {
                setSinglePostData(singlePostInitial);
                setEditPost(singlePost);
              }}
              onEndorsePost={() => setEndorsePost(singlePost, true)}
              single
            />
          </Modal>
        )}
        {
       editPost && (
         <EditPostModal
           post={editPost}
           onHide={() => {
             setEditPost(null);
             setSinglePostData(singlePostInitial);
           }}
         />
       )
     }
        {
       users.data && (
         <UsersModal
           users={users.data}
           modalProps={{
             onHide: () => showUsers({
               loading: false,
               users: {},
             }),
             show: !!users,
             dialogClassName: classes.usersModal,
           }}
           title="They found this post useful"
         />
       )
     }
        {isInfo && (
        <Modal
          headerText="Business Card"
          onClose={() => {
            setUserInfo(userInfoInitial);
            if (!isEmpty(singlePost)) {
              setSinglePostData({
                ...singlePostData,
                isShowSinglePost: true,
              });
            }
          }}
          visible={isInfo}
          modelProps={{ className: 'community-modal', dialogClassName: 'community-member-popup' }}
        >
          <MemberTile member={info} />
        </Modal>
        )}
        {users.loading && <Spinner fixed /> }
      </UserInfoContext.Provider>
    </SinglePostContext.Provider>
  );
});

PostFeedContainer.propTypes = {
  classes: PropTypes.object.isRequired,
};

export default injectStyle(styles)(PostFeedContainer);
