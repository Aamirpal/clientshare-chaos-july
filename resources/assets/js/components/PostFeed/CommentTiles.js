import React, { useContext, useState, useCallback } from 'react';
import get from 'lodash/get';
import isEmpty from 'lodash/isEmpty';
import moment from 'moment';
import classnames from 'classnames';
import reactStringReplace from 'react-string-replace';

import Parser from 'html-react-parser';

import { PostsFeedContext, UsersContext, UserInfoContext } from '../../utils/contexts';
import {
  sortComments, createEditAttachmentFormatWithType, getMbSize, downloadFile,
} from '../../utils/methods';
import { globalConstants } from '../../utils/constants';
import { Image, DocumentPreview } from '../index';
import CommentInput from '../FormInputs/CommentInput';

const { userId } = globalConstants;

const CommentTiles = React.memo(({ classes, postId, onDelete }) => {
  const { postData: { posts } } = useContext(PostsFeedContext);
  const [seeMore, setSeeMore] = useState(false);
  const { users } = useContext(UsersContext);
  const [edit, setEdit] = useState({});
  const [preview, showPreview] = useState(false);
  const { updateUserInfo } = useContext(UserInfoContext);
  const comments = get(posts[postId], 'comments', []);

  const getPreviewData = useCallback(
    (file) => {
      const extension = get(file, 'metadata.extention', '').toLowerCase();
      if (getMbSize(10) > get(file, 'metadata.size') && (extension !== 'csv' && extension !== 'xls' && extension !== 'xlsx' && extension !== 'xlsm')) {
        return showPreview(file);
      }

      return downloadFile(get(file, 'metadata.url', ''), get(file, 'metadata.originalName', ''));
    },
    [],
  );

  return (
    <>
      {sortComments(comments, seeMore).map(comment => (
        <div className={classes.allCommentContainer} key={comment.id}>
          <div onClick={() => updateUserInfo(comment.user_id)}>
            <Image img={get(get(users, comment.user_id), 'user.circular_image_url', null)} size="img36" extraClass={classes.userIcon} />
          </div>
          {edit === comment.id ? (
            <CommentInput
              postId={postId}
              buttonText="Save changes"
              editComment={get(comment, 'comment', '').replace(/\r?<br \/>/g, '')}
              onCancel={() => setEdit(false)}
              editAttachments={createEditAttachmentFormatWithType(comment.attachments)}
              commentId={comment.id}
            />
          ) : (
            <div className={classes.commentDetails}>
              <div className={classes.commentTextContainer}>
                {!isEmpty(users) && (
                <p className={classes.commentText}>
                  <span className={classes.userName} onClick={() => updateUserInfo(comment.user_id)}>{get(get(users, comment.user_id), 'user.fullname')}</span>
                    &nbsp;
                  <span
                    className={classes.commentDescription}
                  >
                    {Parser(get(comment, 'comment', ''), {
                      replace: (domNode) => {
                        if (/@(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})/g.test(get(domNode, 'data'))) {
                          const tags = reactStringReplace(get(domNode, 'data', ''), /@(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})/g, (tag, i) => {
                            if (tag === '00000000-0000-0000-0000-000000000000') {
                              return <a href="javascript:" key={i}>@All</a>;
                            }
                            if (users[tag]) {
                              return (
                                <a href="javascript:" key={i} onClick={() => updateUserInfo(users[tag].user.id)}>{users[tag].user.fullname}</a>
                              );
                            }
                            return tag;
                          });
                          return <span>{tags}</span>;
                        }
                      },
                    })}
                  </span>
                  &nbsp;
                </p>
                )}
                {get(comment, 'attachments', []).map(attach => (
                  <p
                    className={classes.sharedFileName}
                    key={attach.id}
                    onClick={() => getPreviewData({
                      ...attach,
                      post_id: comment.post_id,
                    })}
                  >
                    {attach.file_name}
                  </p>
                ))}
              </div>
              <div className={classnames(classes.commentInfoContainer, 'tag-container')}>
                <p className={classes.commentDate}>
                  {moment(get(comment, 'created_at')).format('MMMM DD, HH:mm')}
                  {get(comment, 'created_at') !== get(comment, 'updated_at') && (
                    <span>&nbsp;(edited)</span>
                  )}
                </p>
                {!!get(comment, 'attachments', []).length && (
                  <p className={classes.commentAttachment}>{`${get(comment, 'attachments', []).length} attachment`}</p>
                )}
                {userId === comment.user_id && (
                  <>
                    <p
                      className={classes.pointer}
                      onClick={() => setEdit(comment.id)}
                    >
                      Edit
                    </p>
                    <p className={classes.pointer} onClick={() => onDelete(comment)}>Delete</p>
                  </>
                )}
              </div>
            </div>
          )}

        </div>
      ))}
      {comments.length > 2
        && (
        <div onClick={() => setSeeMore(!seeMore)} className={classes.seeMoreCommentsWrap}>
            {seeMore ? 'View fewer comments' : 'See more comments'}
        </div>
        )}
      {preview
      && (
      <DocumentPreview
        modelProps={{ show: !!preview, onHide: () => showPreview(false) }}
        file={preview}
      />
      )}
    </>
  );
});

export default CommentTiles;
