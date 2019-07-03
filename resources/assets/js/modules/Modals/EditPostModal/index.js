import React, { useEffect, useState } from 'react';
import get from 'lodash/get';
import PropTypes from 'prop-types';

import { createEditAttachmentFormat } from '../../../utils/methods';
import withData from '../../../utils/hoc/withData';
import AddPost from '../../AddPost';
import { getGroupMembers } from '../../../api/app';
import Spinner from '../../../components/Spinner';

const EditPostModal = ({
  post, groups, categories, onHide,
}) => {
  const imageAttachments = createEditAttachmentFormat(post.images, 'images');
  const videoAttachments = createEditAttachmentFormat(post.videos, 'videos');
  const filesAttachments = createEditAttachmentFormat(post.documents, 'files');
  const [groupData, setGroupData] = useState({ members: [], fetched: false });

  const attachments = {
    ...imageAttachments,
    ...videoAttachments,
    ...filesAttachments,
  };

  useEffect(() => {
    const group = groups[`'${post.group_id}'`] || null;
    if (!get(group, 'is_default') && group) {
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
  return (
    <>
      {fetched ? (
        <AddPost
          editPost={post.id}
          editCategory={get(categories, get(post, 'space_category_id'), {})}
          editGroup={groupData}
          formProps={{
            formEmbed: {
              embedData: get(post, 'url_preview'),
              loading: false,
            },
            formValues: {
              post_subject: get(post, 'post_subject'),
              post_description: get(post, 'post_description', '').replace(/\r?<br \/>/g, '\n'),
              attachments,
            },
          }}
          onCloseEdit={onHide}
          post={post}
        />
      ) : <Spinner fixed />}
    </>
  );
};

EditPostModal.propTypes = {
  post: PropTypes.object.isRequired,
  groups: PropTypes.object.isRequired,
  categories: PropTypes.object.isRequired,
  onHide: PropTypes.func.isRequired,
};

export default withData(EditPostModal);
