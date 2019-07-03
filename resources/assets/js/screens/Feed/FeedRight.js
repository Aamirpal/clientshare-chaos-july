import React, { PureComponent } from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import isEmpty from 'lodash/isEmpty';
import isEqual from 'lodash/isEqual';
import get from 'lodash/get';
import pickBy from 'lodash/pickBy';
import axios from 'axios';
import _values from 'lodash/values';

import withCategoryContext from '../../utils/hoc/withCategoryContext';
import {
  GroupContext, UpdateGroupContext, PostsFeedContext, CategoryContext,
} from '../../utils/contexts';
import {
  getGroupList,
  getGroupMembers,
  groupDelete,
  getFeedPosts,
} from '../../api/app';
import { Button } from '../../components';
import { AddIcon, EditIcon } from '../../images';
import FeedHeader from '../../modules/FeedHeader';
import ConfirmationModal from '../../components/Modal/confirmationModal';
import BusinessReviews from './BusinessReviews';

import {
  AddPost,
  CreateGroupModal,
  GroupTile,
  EditGroupModal,
  PostFeedContainer,
} from '../../modules';

const initialModals = {
  isConfirm: false,
  isEdit: false,
  addGroup: false,
  discard: false,
};

const initialPostData = {
  posts: [],
  offset: 0,
  space_groups: {},
};

class Feed extends PureComponent {
  prevApi = {}

  isFeedRunning = false;

  static propTypes = {
    classes: PropTypes.object.isRequired,
    category: PropTypes.object.isRequired,
    businessReviews: PropTypes.bool.isRequired,
  }

  constructor(props) {
    super(props);
    this.state = {
      isEditDelete: false,
      groups: {},
      editItem: {},
      allowCurrentUserPost: false,
      selectedGroup: null,
      modals: initialModals,
      lastModal: null,
      editGroupValues: {},
      type: 'create',
      activeGroup: 0,
      isSinglePostDone: false,
      postData: initialPostData,
      hasMoreFeeds: true,
      groupMembers: [],
    };
  }

  componentDidMount() {
    this.getGroups();
  }

  componentDidUpdate(prevProps, prevState) {
    const { activeGroup } = this.state;
    const { category: { categoryId }, businessReviews } = this.props;
    if ((!businessReviews)
    && ((categoryId !== prevProps.category.categoryId) || (activeGroup !== prevState.activeGroup))) {
      // Reset Feed Filter
      this.setState(state => ({ 
        hasMoreFeeds: true,
        postData: {
          ...initialPostData,
          space_groups: state.postData.space_groups,
        },
      }));
    }
    // When Category Changes
    if (categoryId !== prevProps.category.categoryId) { 
      document.body.scrollTop = 0; // For Safari
      document.documentElement.scrollTop = 0;
      this.setState({activeGroup: null});
    }
    if (businessReviews) {
      this.prevApi = null;
    }
  }

  getPosts = (index, added = false) => {
    if (added) {
      this.prevApi = null;
      document.body.scrollTop = 0; // For Safari
      document.documentElement.scrollTop = 0;
      return this.setState({ hasMoreFeeds: true, postData: initialPostData });
    }
    const { category: { categoryId } } = this.props;
    const { postData: { offset, posts }, activeGroup } = this.state;
    // eslint-disable-next-line max-len
    if ((!isEqual(this.prevApi, { group_id: activeGroup, space_category_id: categoryId, offset }))) {
      this.prevApi = { group_id: activeGroup, space_category_id: categoryId, offset };
      const { CancelToken } = axios;
      const source = CancelToken.source();
      if (this.isFeedRunning) {
        this.isFeedRunning.cancel('Operation canceled by the user.');
      }
      this.isFeedRunning = source;
      const params = {
        offset,
      };
      if (activeGroup) {
        params.group_id = activeGroup;
      }
      if (categoryId) {
        params.space_category_id = categoryId;
      }
      getFeedPosts(params, source)
        .then(({ data }) => {
          this.isFeedRunning = false;
          this.setState(postData => ({
            postData: {
              ...postData,
              ...data,
              posts: {
                ...posts,
                ...data.posts,
              },
            },
            hasMoreFeeds: _values(data.posts).length > 2,
          }));
        }).catch(() => false);
    }
    return false;
  }

  updatePosts = (incomingPosts) => {
    this.setState(state => ({
      postData: {
        ...state.postData,
        posts: incomingPosts,
      },
    }));
  }


  showAddGroupModal = () => {
    this.toggleModals('addGroup', { editItem: {}, type: 'create' });
  };

  openEditModal = () => {
    this.toggleModals('isEdit', { type: 'edit' });
  };

  hideAddGroupModal = () => {
    const { editItem } = this.state;
    const updatedModals = { addGroup: false };
    if (!isEmpty(editItem)) {
      updatedModals.isEdit = true;
    }
    this.setState(() => updatedModals);
  };

  getGroups = () => {
    getGroupList().then(({ data: { groups, allow_current_user_post } }) => {
      this.setState(() => ({ groups, allowCurrentUserPost: allow_current_user_post, modals: initialModals }));
    });
  };

  fetchGroups = () => {
    getGroupList().then(({ data: { groups, allow_current_user_post } }) => {
      this.setState(() => ({
        groups,
        allowCurrentUserPost: allow_current_user_post,
      }));
    });
  };

  handleUpdateGroups = () => {
    this.toggleModals('isEdit', {
      editItem: {},
    });
    getGroupList().then(({ data: { groups, allow_current_user_post } }) => {
      this.setState(() => ({
        groups,
        allowCurrentUserPost: allow_current_user_post,
      }));
    });
  };

  handleEditGroup = (item) => {
    if (item && item.id > 0) {
      this.toggleModals('addGroup', {
        editItem: {},
      });
      document.getElementsByClassName(
        'modal-backdrop',
      )[0].style.backgroundColor = 'red';
      getGroupMembers(item.id).then((res) => {
        if (res && res.data) {
          this.setState({
            editItem: {
              name: item.name || '',
              group_id: item.id,
              members: res.data.group_members || [],
            },
            editGroupValues: {
              ...item,
              members: res.data.group_members,
            },
          });
        }
      });
    }
  };

  manageEditDelete = (groupId, action, type = 'open') => {
    if (action && groupId > 0 && type === 'delete') {
      this.setState({ isEditDelete: action });
      groupDelete(groupId)
        .then(() => {
          this.getGroups();
          this.setState({
            editItem: {},
            isEditDelete: false,
          });
        })
        .catch(() => {
          this.setState({ isEditDelete: false });
        });
    } else if (action && groupId > 0 && type === 'close') {
      this.setState({ isEditDelete: action });
    } else {
      this.setState({ isEditDelete: action });
    }
  };

  deleteGroup = () => {
    const { selectedGroup } = this.state;
    const updatedModal = {};
    groupDelete(selectedGroup)
      .then(() => {
        getGroupList().then(({ data: { groups, allow_current_user_post } }) => {
          if (groups.length) {
            updatedModal.isEdit = true;
          }
          this.setState(() => ({
            groups,
            allowCurrentUserPost: allow_current_user_post,
            modals: {
              ...initialModals,
              ...updatedModal,
            },
          }));
        });
      })
      .catch(() => {
        this.setState({ isEditDelete: false });
      });
  };

  showConfirm = (groupId, value, members) => {
    const { editItem } = this.state;
    this.toggleModals('isConfirm', {
      selectedGroup: groupId,
      editItem: { ...editItem, members, name: value },
    });
  };

  toggleModals = (modelName, extraState = {}) => {
    const { modals, lastModal } = this.state;
    const pickModal = Object.keys(pickBy(modals, value => value === true));

    let updatedLastModal = lastModal;
    if (pickModal[0] !== 'isConfirm' && pickModal[0] !== 'discard') {
      updatedLastModal = pickModal[0];
    }
    this.setState(() => ({
      modals: {
        ...initialModals,
        [modelName]: true,
      },
      lastModal: updatedLastModal || null,
      ...extraState,
    }));
  };

  hideModals = () => {
    this.setState(() => ({ modals: initialModals }));
  };

  hideConfirmModal = (type) => {
    const { lastModal } = this.state;
    if (type === 'create') {
      return this.hideModals();
    }
    if (type === 'edit') {
      return this.toggleModals('isEdit');
    }
    if (lastModal) {
      return this.toggleModals(lastModal);
    }
    return false;
  };

  manageChanges = (value, members) => {
    const { editItem } = this.state;
    this.toggleModals('discard', {
      editItem: { ...editItem, members, name: value },
    });
  };

  successDiscard = () => {
    this.toggleModals('isEdit', { editItem: {} });
  };

  setSelectedGroup = (groupId) => {
    // setItem('group', groupId);
    this.setState(() => ({ activeGroup: groupId }));
    getGroupMembers(groupId).then((res) => {
      if (res && res.data) {
        this.setState(() => ({ groupMembers: res.data.group_members }));
      }
    }).catch(() => false);
  }

  render() {
    const {
      groups,
      editItem,
      isEditDelete,
      allowCurrentUserPost,
      modals: {
        isConfirm, addGroup, isEdit, discard,
      },
      editGroupValues,
      type,
      activeGroup,
      postData,
      hasMoreFeeds,
      groupMembers,
    } = this.state;
    const { classes, businessReviews, category: { categoryId } } = this.props;
    return (
      <GroupContext.Provider value={groups}>
        <PostsFeedContext.Provider value={{
          postData,
          fetchPosts: this.getPosts,
          hasMoreFeeds,
          updatePosts: this.updatePosts,
        }}
        >
          <UpdateGroupContext.Provider
            value={{ updateGroup: this.setSelectedGroup, groupId: activeGroup, groupMembers }}
          >

            {businessReviews ? (
              <BusinessReviews allowCurrentUserPost={allowCurrentUserPost} />
            ) : (
              <>
                <div className="feed-post-wrap">
                  <div className="feed-right-part">
                    <CategoryContext.Consumer>
                      {category => (
                        (!isEmpty(category) && categoryId) ? (
                          <FeedHeader
                            icon={`/${category[categoryId].category_logo}`}
                            text={category[categoryId].category_name}
                            category={category}
                          />
                        ) : (
                          <div className="feed-header">
                            <div className="feed-head-left" />
                          </div>
                        )
                      )}
                    </CategoryContext.Consumer>
                    {(Boolean(categoryId) && (_values(groups).length > 1 && allowCurrentUserPost)) && (
                    <div className="feed-head-right">
                      <Button
                        icon={AddIcon}
                        buttonProps={{
                          variant: 'secondary',
                          onClick: this.showAddGroupModal,
                        }}
                      >
                        Create a group
                      </Button>
                      <Button
                        icon={EditIcon}
                        buttonProps={{
                          variant: 'secondary',
                          onClick: this.openEditModal,
                        }}
                      >
                        Edit groups
                      </Button>
                    </div>
                    )}
                  </div>
                  {Boolean(categoryId) && (
                    <GroupTile
                      groups={groups}
                      createGroupButton={this.showAddGroupModal}
                      allowUserPost={allowCurrentUserPost}
                    />
                  )}
                  <div className={classes.feedContainer}>
                    {allowCurrentUserPost && <AddPost />}
                    <PostFeedContainer />
                  </div>

                  {addGroup && (
                  <CreateGroupModal
                    modelProps={{
                      show: addGroup,
                      onHide: this.hideConfirmModal,
                      className: 'lg-popup',
                    }}
                    editItem={editItem}
                    onSuccess={this.getGroups}
                    updateGroup={this.handleUpdateGroups}
                    manageEditDelete={this.showConfirm}
                    isEditDelete={isEditDelete}
                    onDiscard={this.manageChanges}
                    fetchGroups={this.fetchGroups}
                    editGroupValues={editGroupValues}
                    type={type}
                  />
                  )}
                  {isEdit && (
                  <EditGroupModal
                    modelProps={{
                      show: isEdit,
                      onHide: this.hideModals,
                      className: 'lg-popup',
                    }}
                    handleEdit={this.handleEdit}
                    handleEditGroup={this.handleEditGroup}
                    updateGroup={this.handleUpdateGroups}
                    isEdit={isEdit}
                    deleteGroup={this.showConfirm}
                    isConfirmProps={isConfirm}
                  />
                  )}
                </div>
                <ConfirmationModal
                  changesRequest={false}
                  message="Do you want to permanently delete this group?"
                  headerText="Delete group"
                  buttonCancel="Cancel"
                  modelProps={{
                    show: isConfirm,
                    className: 'sm-popup',
                    onHide: this.hideConfirmModal,
                  }}
                  onSuccess={this.deleteGroup}
                  onCancel={this.hideConfirmModal}
                />
                <ConfirmationModal
                  changesRequest
                  message="Are you sure you want to leave without saving your changes?"
                  headerText="Leave witout saving changes"
                  buttonCancel="Yes"
                  modelProps={{
                    show: discard,
                    className: 'sm-popup leave-modal',
                    onHide: this.hideConfirmModal,
                  }}
                  onSuccess={() => this.toggleModals('addGroup')}
                  onCancel={this.successDiscard}
                />
              </>
            )}
          </UpdateGroupContext.Provider>
        </PostsFeedContext.Provider>
      </GroupContext.Provider>
    );
  }
}

const styles = {
  feedContainer: {
    marginTop: 4,
    maxWidth: 736,
    '@media (max-width: 767px)': {
      marginTop: 0,
      width: '100%',
    },
  },
};

export default withCategoryContext(injectSheet(styles)(Feed));
