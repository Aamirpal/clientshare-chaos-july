import React, { useState, useContext } from 'react';
import isEmpty from 'lodash/isEmpty';
import _values from 'lodash/values';
import PropTypes from 'prop-types';
import MediaQuery from 'react-responsive';
import {
  GroupContext, UpdateCategoryContext, UpdateGroupContext, PostsFeedContext,
} from '../../utils/contexts';
import AddPostDisabled from '../../components/Forms/AddPost/disabled';
import { SelectCategoryModal, SelectGroupModal } from '../Modals';
import AddPostForm from '../../components/Forms/AddPost';
import ConfirmationModal from '../../components/Modal/confirmationModal';
import { getGroupMembers, shareProfileProgress } from '../../api/app';

const initialModalStates = {
  categoryModal: false, groupModal: false, postModal: false, discardModal: false,
};
const intialModals = {
  ...initialModalStates, lastModal: '', isChange: false,
};

const AddPost = React.memo(({
  editPost, editCategory, editGroup, formProps, onCloseEdit, post,
}) => {
  const groups = useContext(GroupContext);
  const categoryAction = useContext(UpdateCategoryContext);
  const groupAction = useContext(UpdateGroupContext);
  const postActions = useContext(PostsFeedContext);

  const [modals, setModals] = useState({ ...intialModals, postModal: !!editPost });
  const [categoryData, setCategoryData] = useState(editCategory);
  const [groupData, setGroupData] = useState(editGroup);

  const toggleModals = (type, updatedState = {}) => {
    setModals(previousModals => ({
      ...previousModals,
      ...initialModalStates,
      [type]: true,
      ...updatedState,
    }));
  };

  const hideAllModals = () => setModals(intialModals);

  const resetAllState = () => {
    hideAllModals();
    setCategoryData({});
    setGroupData({});
    if (editPost) {
      onCloseEdit();
    }
  };

  const selectCategory = (category) => {
    const groupArray = _values(groups);
    setCategoryData(category);
    if (!isEmpty(categoryData)) {
      return toggleModals('postModal');
    }
    if (groupArray.length > 1) {
      return toggleModals('groupModal');
    }
    setGroupData(groupArray[0]);
    return toggleModals('postModal');
  };

  const checkDiscard = (lastModal) => {
    if (!modals.isChange) {
      return resetAllState();
    }
    return toggleModals('discardModal', { lastModal });
  };

  const checkDiscardPost = (lastModal, isChange) => {
    if (!isChange) {
      return resetAllState();
    }
    return toggleModals('discardModal', { lastModal, isChange });
  };


  const selectGroup = (group) => {
    setGroupData(group);
    if (!group.is_default) {
      getGroupMembers(group.id).then(({ data: { group_members } }) => {
        const updateGroup = {
          ...group,
          members: group_members,
        };
        setGroupData(updateGroup);
      });
    }
    toggleModals('postModal');
  };

  const successInsertPost = (response) => {
    shareProfileProgress().then(({ data: { posts_count } }) => {
      if (posts_count === 5) {
        window.location.reload();
      }
    }).catch(() => false);
    const postData = response.data;
    if (categoryAction.categoryId) {
      if (categoryAction.categoryId === postData.space_category_id
        && groupAction.groupId === postData.group_id) {
        postActions.fetchPosts(null, true);
      } else {
        categoryAction.updateCategory({ category_id: postData.space_category_id, category_name: '' });
        groupAction.updateGroup(postData.group_id);
      }
    } else {
      postActions.fetchPosts(null, true);
    }

    return resetAllState();
  };

  const toggleIncomingModal = (isDiscard, incomingModal) => {
    toggleModals(incomingModal, {
      lastModal: incomingModal,
      isChange: isDiscard,
    });
  };


  const {
    categoryModal, groupModal, postModal, discardModal, lastModal,
  } = modals;
  return (
    <>
      <AddPostDisabled onClick={() => toggleModals('categoryModal')} disabled />
      {categoryModal && (
      <SelectCategoryModal
        modelProps={{ show: categoryModal, onHide: () => checkDiscard('categoryModal') }}
        onSelect={selectCategory}
        active={categoryData.category_id}
        editPost={editPost}
      />
      )}

      {groupModal && (
        <SelectGroupModal
          modelProps={{ show: groupModal, onHide: () => checkDiscard('groupModal') }}
          onSelect={selectGroup}
          active={groupData.id}
          editPost={editPost}
        />
      )}

      {(categoryModal || groupModal || postModal || discardModal) && (
      <AddPostForm
        modelProps={{ show: postModal, onHide: isDiscard => checkDiscardPost('postModal', isDiscard) }}
        category={categoryData}
        group={groupData}
        onCategoryClick={discard => toggleIncomingModal(discard, 'categoryModal')}
        onGroupClick={discard => toggleIncomingModal(discard, 'groupModal')}
        onSuccess={successInsertPost}
        {...formProps}
        editPost={editPost}
        post={post}
      />
      )}
      <MediaQuery query="(min-device-width: 767px)">
        {discardModal && (
          <ConfirmationModal
            message="Are you sure you want to discard this post, the post content will be lost?"
            headerText="Discard post"
            buttonCancel="Cancel"
            modelProps={{ show: discardModal, className: 'sm-popup', onHide: () => toggleModals(lastModal) }}
            onSuccess={resetAllState}
            onCancel={() => toggleModals(lastModal)}
            buttonText="Discard post"
          />
        )}
      </MediaQuery>
      <MediaQuery query="(max-device-width: 767px)">
        {discardModal && (
          <ConfirmationModal
            message="Are you sure you want to discard this post?"
            headerText="Discard post"
            buttonCancel="Cancel"
            modelProps={{ show: discardModal, className: 'sm-popup', onHide: () => toggleModals(lastModal) }}
            onSuccess={resetAllState}
            onCancel={() => toggleModals(lastModal)}
            buttonText="Discard post"
          />
        )}
      </MediaQuery>
    </>
  );
});

AddPost.defaultProps = {
  editPost: null,
  editCategory: {},
  editGroup: { members: [] },
  formProps: {},
  onCloseEdit: () => {},
  post: null,
};

AddPost.propTypes = {
  editPost: PropTypes.any,
  editCategory: PropTypes.object,
  editGroup: PropTypes.object,
  formProps: PropTypes.object,
  onCloseEdit: PropTypes.func,
  post: PropTypes.object,
};

export default AddPost;
