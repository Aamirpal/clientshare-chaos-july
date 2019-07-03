import React, { useState, useContext } from 'react';
import _values from 'lodash/values';
import MediaQuery from 'react-responsive';
import Disabled from '../../components/Forms/AddPost/disabled';
import BusinessGroupModal from '../../modules/Modals/BusinessGroupModal';
import ReviewPostModal from '../../modules/Modals/ReviewPostModal';
import ConfirmationModal from '../../components/Modal/confirmationModal';
import Icon from '../../components/Icon';
import { getGroupMembers } from '../../api/app';
import { GroupContext, BusinessReviewContext } from '../../utils/contexts/index';
import addIcon from '../../images/add_small_icon.svg';
import './business_review.scss';

const initialModals = { businessGroupModal: false, addReviewPostModal: false, discardModal: false };
const BusinessReviewsModal = React.memo(({
  editReviewPost,
  editGroup,
  formValues,
  onCloseEdit,
}) => {
  const [modals, setModals] = useState({ ...initialModals, addReviewPostModal: !!editReviewPost });
  const [groupData, setGroupData] = useState(editGroup);
  const groups = useContext(GroupContext);
  const reviewActions = useContext(BusinessReviewContext);

  const toggleModals = (modelName, value = null, key = '') => {
    setModals(prevData => ({
      ...initialModals,
      [modelName]: true,
    }));
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
    toggleModals('addReviewPostModal');
  };

  const resetModals = (res) => {
    setModals(initialModals);
    if (editReviewPost) {
      onCloseEdit(res);
    }
  };

  const afterReviewSubmit = (res) => {
    resetModals(res);
    reviewActions.fetchReviews(null, true);
  };

  const checkDiscard = (change) => {
    if (change) {
      return toggleModals('discardModal');
    }
    return resetModals();
  };

  const insertReview = () => {
    if (_values(groups).length < 2) {
      return toggleModals('addReviewPostModal');
    }
    return toggleModals('businessGroupModal');
  };

  const { businessGroupModal, addReviewPostModal, discardModal } = modals;
  return (
    <>
      <MediaQuery query="(min-width: 767px)">
        <Disabled attachBox={false} onClick={insertReview} />
      </MediaQuery>
      <MediaQuery query="(max-width: 767px)">
        <div className="add-review-btn-mbl" onClick={insertReview}>
          <span className="log-review">
            <Icon path={addIcon} />
            Log a review
          </span>
        </div>
      </MediaQuery>
      <BusinessGroupModal
        modelProps={{ show: businessGroupModal, onHide: resetModals }}
        onSelect={selectGroup}
        active={groupData.id}
      />
      {(addReviewPostModal || businessGroupModal || discardModal) && (
        <ReviewPostModal
          modelProps={{ show: addReviewPostModal, onHide: checkDiscard }}
          group={groupData}
          onGroupClick={() => toggleModals('businessGroupModal')}
          onSuccess={afterReviewSubmit}
          formValues={editReviewPost ? formValues : null}
          editReviewPost={editReviewPost}
        />
      )}
      {discardModal && (
        <ConfirmationModal
          message="Are you sure you want to discard this review?"
          headerText="Discard review"
          buttonCancel="Cancel"
          modelProps={{ show: discardModal, className: 'sm-popup', onHide: () => toggleModals('addReviewPostModal') }}
          onSuccess={resetModals}
          onCancel={() => toggleModals('addReviewPostModal')}
          buttonText="Discard review"
        />
      )}
    </>
  );
});

BusinessReviewsModal.defaultProps = {
  editReviewPost: null,
  editGroup: { members: [] },
  formValues: {},
  onCloseEdit: () => {},
};

export default BusinessReviewsModal;
