import React, { useEffect, useState, useContext } from 'react';
import get from 'lodash/get';
import PropTypes from 'prop-types';

import { createEditAttachmentFormat } from '../../../utils/methods';
import { UsersContext } from '../../../utils/contexts';
import withData from '../../../utils/hoc/withData';
//import ReviewPostModal from '../ReviewPostModal';
import BusinessReviewsModal from '../../../screens/Feed/AddReviewModal';
import { getGroupMembers } from '../../../api/app';
import Spinner from '../../../components/Spinner';

const EditReviewModal = ({
  reviewData, groups, onHide,
}) => {
  const { users } = useContext(UsersContext);
  const imageAttachments = createEditAttachmentFormat(reviewData.images, 'images');
  const videoAttachments = createEditAttachmentFormat(reviewData.videos, 'videos');
  const filesAttachments = createEditAttachmentFormat(reviewData.documents, 'files');
  const [groupData, setGroupData] = useState({ members: [], fetched: false });

  const attachments = {
    ...imageAttachments,
    ...videoAttachments,
    ...filesAttachments,
  };

  useEffect(() => {
    const group = groups[`'${reviewData.group_id}'`] || null;
    if (!group.is_default && group) {
      getGroupMembers(group.id).then(({ data: { group_members } }) => {
        const updateGroup = {
          ...group,
          members: group_members,
          fetched: true,
        };
        setGroupData(updateGroup);
      });
    } else {
      const updateGroup = {
        ...group,
        members: [],
        fetched: true,
      };
      setGroupData(updateGroup);
    }
  }, []);


  const { fetched } = groupData;

  const modifyMembers = get(reviewData, 'attendees', []).map((atten) => {
    const user = get(users, get(atten, 'space_user.user_id'), {});
    return ({
      full_name: get(user, 'user.fullname', ''),
      user_id: atten.space_user.user_id,
      id: atten.space_user_id,
    });
  });
  return (
    <>
      {fetched ? (
        <BusinessReviewsModal
          editReviewPost={reviewData.id}
          editGroup={groupData}
          onCloseEdit={onHide}
          formValues={{
            title: reviewData.title,
            review_date: reviewData.review_date,
            description: reviewData.description.replace(/\r?<br \/>/g, '\n'),
            attachments,
            conducted_via: reviewData.conducted_via,
            attendees: modifyMembers,
          }}
        />
      ) : <Spinner fixed />}
    </>
  );
};

EditReviewModal.propTypes = {
  reviewData: PropTypes.object.isRequired,
  groups: PropTypes.object.isRequired,
  onHide: PropTypes.func.isRequired,
};

export default withData(EditReviewModal);
