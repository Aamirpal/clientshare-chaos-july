import React, {
  useState, useContext, useRef, useEffect,
} from 'react';
import injectStyle from 'react-jss';
import Dropzone from 'react-dropzone';
import Mention, { toEditorState, toString } from 'rc-editor-mention';
import _values from 'lodash/values';
import isEmpty from 'lodash/isEmpty';
import isEqual from 'lodash/isEqual';
import get from 'lodash/get';
import classnames from 'classnames';
import axios from 'axios';
import uuid from 'uuid';
import { ContentState, convertToRaw, convertFromHTML } from 'draft-js';

import {
  ProgressBar, AttachmentTile, Button, ConfirmationModal,
} from '../../index';
import {
  UsersContext, PostsFeedContext, UpdateGroupContext, GroupContext,
} from '../../../utils/contexts';
import withTheme from '../../../utils/hoc/withTheme';
import {
  addComment, getS3Token, deleteAttachment, editCommentApi,
} from '../../../api/app';
import { fileUpload } from '../../../api/s3';
import { ImgAttachment } from '../../../images';
import {
  seperateFiles, convertAttachmentData, convertIdtoNameString, findKey,
} from '../../../utils/methods';
import { styles } from './styles';

const { Nav } = Mention;
const Tag = withTheme(({ data, children, theme }) => {
  const { users } = useContext(UsersContext);
  const userid = data.user_id;
  return (
    <span style={{ color: theme.primary_color }}>
      {`@${get(users[userid], 'user.fullname', 'All')}`}
      <span style={{ display: 'none' }}>
        {children}
      </span>
    </span>
  );
});

let wrapperRef = null;

const CommentInput = React.memo(({
  classes, postId, buttonText, editComment, editAttachments, onCancel, commentId,
}) => {
  const [isLoaded, setLoaded] = useState(false);
  const [isError, setError] = useState(null);
  const { users } = useContext(UsersContext);
  const { groupMembers } = useContext(UpdateGroupContext);
  const groups = useContext(GroupContext);
  const updateUsers = {
    '00000000-0000-0000-0000-000000000000': {
      user_id: '00000000-0000-0000-0000-000000000000',
      user: {
        fullname: 'All',
        id: '00000000-0000-0000-0000-000000000000',
      },
      company: {
        company_name: 'Everyone included in this post',
      },
    },
    ...users,
  };
  const postContext = useContext(PostsFeedContext);
  const { postData: { posts }, updatePosts } = postContext;
  const isDefault = get(groups, `'${get(posts[postId], 'group_id')}'.is_default`);
  const getSuggestions = suggestUsers => suggestUsers.map(el => (
    <Nav value={get(el, 'user.fullname')} key={el.user_id} data={el}>
      <div className="tag-row">
        <span className="meta">
          { el.user.fullname}
          {(!findKey(groupMembers, el.user_id, 'user_id') && !isDefault && el.user_id !== '00000000-0000-0000-0000-000000000000') && (
            <span className="company-name">(Cannot view content in this group)</span>
          )}
        </span>
        <span className="company-name">{ get(el, 'company.company_name')}</span>
      </div>
    </Nav>
  ));

  const getValue = () => {
    if (commentId && !isLoaded) {
      setLoaded(true);
      const pattern = /(@(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})+)/g;
      const patternWithPrefix = /(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})/g;
      const alt = ContentState.createFromText('');
      const txt = editComment.split(' ');
      const withTags = txt
        .filter((item) => {
          const match = item.match(pattern);
          if (match) {
            const splitTag = match[0].split('@');
            if (updateUsers[splitTag[1]]) {
              return true;
            }
            return false;
          }
          return false;
        }).map((item) => {
          const matchId = item.match(pattern);
          const splitTag = matchId[0].split('@');
          alt.createEntity('mention', 'IMMUTABLE', updateUsers[splitTag[1]]);
          const key = alt.getLastCreatedEntityKey();
          return { key, item: splitTag[1] };
        });
      let currentTag = -1;
      const actualComment = convertIdtoNameString(editComment, updateUsers);
      const blocksFromHTML = convertFromHTML(`${actualComment}`);
      const cs = ContentState.createFromBlockArray(
        blocksFromHTML.contentBlocks,
        blocksFromHTML.entityMap,
      );
      let block = cs.getBlocksAsArray()[0];
      let list = block.getCharacterList();
      let isSet = 0;
      let actualIterator = 0;
      let isLoop = 0;
      const names = [];
      const convertTag = () => {
        if (editComment[isLoop] === '@' && isSet === 0) {
          const remainingText = editComment.substr(isLoop, editComment.length);
          const splitComment = remainingText.split('@')[1].split(' ');
          const matchId = splitComment[0].match(patternWithPrefix);
          if (matchId) {
            if (updateUsers[matchId[0]]) {
              currentTag += 1;
              isSet = updateUsers[matchId[0]].user.fullname.length + 1;
              names.push(isSet);
              if (currentTag > 0) {
                actualIterator = actualIterator - matchId[0].length + get(names, currentTag - 1, 0) - 1;
              }
            }
          }
        }
        if (isSet > 0) {
          isSet -= 1;
          const meta = list.get(actualIterator).set('entity', withTags[currentTag].key);
          list = list.set(actualIterator, meta);
        }
        if (isLoop < editComment.length) {
          actualIterator += 1;
          isLoop += 1;
          convertTag();
        }
      };
      convertTag();
      block = block.set('characterList', list);
      return ContentState.createFromBlockArray([block]);
    }
    return null;
  };

  const [editorState, setEditorState] = useState(editComment || toEditorState(''));
  const [loading, setLoading] = useState(false);
  const [defaultValue] = useState(editComment ? getValue() : null);
  const [suggestions, setSuggestions] = useState(getSuggestions(_values(users)));
  const [attachments, setAttachments] = useState(editAttachments || {});
  const mentionRef = useRef(null);
  const [deletedAttachments, setDeleteAttachments] = useState([]);
  const [discardModal, setDiscardModal] = useState(false);

  const handleClickOutside = (event) => {
    if (wrapperRef && !wrapperRef.contains(event.target) && get(event, 'target.className', null) !== 'meta' && get(event, 'target.className', null) !== 'tag-row') {
      const actualComment = convertIdtoNameString(editComment, updateUsers);
      if (mentionRef.current._editor._editorWrapper.innerText !== actualComment || !isEmpty(deletedAttachments)) {
        setDiscardModal(true);
      } else {
        onCancel();
      }
    }
  };

  useEffect(() => {
    if (commentId) {
      document.addEventListener('mousedown', handleClickOutside);
      return () => {
        document.removeEventListener('mousedown', handleClickOutside);
      };
    }
  }, commentId);

  const setWrapperRef = (node) => {
    wrapperRef = node;
  };

  const onChange = (value) => {
    let commentText = value;
    if (typeof value === 'object') {
      commentText = toString(value, { encode: true });
    }
    commentText = commentText.trim();
    if (commentText) {
      setError(null);
    }
    setEditorState(value);
  };

  const getToken = async () => getS3Token().then(({ data }) => data);

  const onSearchChange = (value) => {
    const splitValue = value.split('@');
    const keyword = splitValue[splitValue.length - 1];
    const suggestionFind = _values(updateUsers).filter(
      el => el.user.fullname.toLowerCase().includes(keyword.toLowerCase()),
    );
    const checkSuggestUsers = getSuggestions(suggestionFind);

    setSuggestions(checkSuggestUsers);
  };

  const cancelRequest = ({ source }) => {
    source.cancel();
  };

  const deleteAttachmentApi = (attach) => {
    delete attachments[attach.id];
    setAttachments({
      ...attachments,
    });
    if (!get(attach, 'exact')) {
      const url = attach.PostResponse.Location['#text'];
      deleteAttachment({ url }).catch(() => {});
    } else {
      deletedAttachments.push(attach);
      setDeleteAttachments([
        ...deletedAttachments,
      ]);
    }
  };

  const onDrop = (acceptedFiles) => {
    if (acceptedFiles.length) {
      getToken().then((updatedToken) => {
        acceptedFiles.forEach((file) => {
          const attachmentKey = uuid.v4();
          const { CancelToken } = axios;
          const source = CancelToken.source();
          fileUpload(file, updatedToken, ({ progress }) => {
            setAttachments(previousattachments => ({
              ...previousattachments,
              [attachmentKey]: {
                type: 'loaders',
                progress,
                source,
                id: attachmentKey,
              },
            }));
          }, source).catch(() => {
            setAttachments((previousattachments) => {
              const checkAttachments = previousattachments;
              if (checkAttachments[attachmentKey]) {
                delete checkAttachments[attachmentKey];
              }
              return ({
                ...checkAttachments,
              });
            });
          }).then((res) => {
            if (res) {
              setAttachments(previousattachments => ({
                ...previousattachments,
                [attachmentKey]: res,
              }));
              setError(null);
            }
          });
        });
      });
    } else {
      // Invalid files
    }
  };

  const sendComment = () => {
    let commentText = editorState;
    if (typeof commentText === 'object') {
      commentText = toString(editorState, { encode: true });
      commentText = commentText.trimStart();
      const { entityMap } = convertToRaw(editorState);
      _values(entityMap).forEach((entity) => {
        commentText = commentText.replace(entity.data.user.fullname, entity.data.user.id);
      });
    }

    if (commentText || !isEmpty(attachments)) {
      setError(null);
      setLoading(true);
      const commentData = {
        comment: commentText,
        post_id: postId,
        attachments: convertAttachmentData(attachments),
      };
      if (commentId) {
        const actualComment = convertIdtoNameString(editComment, updateUsers);
        if ((mentionRef.current._editor._editorWrapper.innerText !== actualComment) || (!isEmpty(deletedAttachments) || (!isEqual(editAttachments, attachments)))) {
          commentData.delete_attachments = deletedAttachments;
          return editCommentApi(commentData, commentId).then(({ data }) => {
            const findIndex = get(posts[postId], 'comments', []).findIndex(comm => comm.id === commentId);
            if (data) {
              posts[postId].comments[findIndex] = data;
            }
            setEditorState(toEditorState(''));
            setAttachments({});
            mentionRef.current.reset();
            setLoading(false);
            onCancel();
          }).catch(() => setLoading(false));
        }
        return onCancel();
      }
      addComment(commentData).then(({ data }) => {
        if (data) {
          posts[postId].comments.push(data);
          updatePosts(posts);
        }
        setEditorState(toEditorState(''));
        setAttachments({});
        mentionRef.current.reset();
        setLoading(false);
      }).catch(() => setLoading(false));
    } else {
      setError('Please Add Attachment or comment');
    }
  };

  const {
    loaders, images, files, videos,
  } = seperateFiles(attachments);

  let checkText = '';
  if (typeof editorState === 'object') {
    checkText = toString(editorState, { encode: true });
  }
  return (
    <div className={classes.inputContainer} ref={setWrapperRef}>
      <Mention
        onSearchChange={onSearchChange}
        defaultValue={defaultValue}
        onChange={onChange}
        suggestions={suggestions}
        prefix="@"
        multiLines
        className={classes.commentInput}
        mode="immutable"
        tag={Tag}
        placeholder="Add a comment or tag someone using @"
        ref={mentionRef}
        notFoundContent="No User Found"
      />
      {isError && (<div className={classes.commentError}>Please Add Attachment or comment</div>)}
      <div className={classnames(classes.buttonContainer, 'attachment-btn')}>
        <Dropzone onDrop={onDrop} accept=".pdf,.docx,.ppt,.pptx,.mp4,.doc,.xls,.xlsx,.csv,.mov,.MOV,.png,.jpeg,.jpg">
          {({ getRootProps, getInputProps }) => (
            <>
              <div {...getRootProps()}>
                <input {...getInputProps()} />
                <Button icon={ImgAttachment} buttonProps={{ variant: 'light', className: classes.button }} rounded> Attach Files </Button>
              </div>
            </>
          )}
        </Dropzone>
      </div>
      <div className="attachments-container">
        <ProgressBar loaders={loaders} cancelRequest={cancelRequest} extraClass="progress-bar-wrap" />
        <AttachmentTile files={images} onDeleteAttachment={deleteAttachmentApi} isDelete showIcon={false} extraClass="file-attachment-wrap" />
        <AttachmentTile files={videos} onDeleteAttachment={deleteAttachmentApi} isDelete showIcon={false} extraClass="file-attachment-wrap" />
        <AttachmentTile files={files} onDeleteAttachment={deleteAttachmentApi} isDelete showIcon={false} extraClass="file-attachment-wrap" />
      </div>
      <div className="comment-btn-wrap">
        {(checkText || !isEmpty(attachments)) && (
          <Button buttonProps={{ onClick: sendComment, className: classnames(classes.addCommentButton, 'add-comment-button'), disabled: loading || loaders.length }}>
            {buttonText}
          </Button>
        )}
      </div>
      {discardModal && (
        <ConfirmationModal
          message="Do you want to discard this comment?"
          headerText="Discard Comment"
          buttonCancel="Cancel"
          modelProps={{ show: discardModal, className: 'sm-popup', onHide: () => setDiscardModal(false) }}
          onSuccess={() => onCancel()}
          onCancel={() => setDiscardModal(false)}
          buttonText="Discard comment"
        />
      )}
    </div>
  );
});

CommentInput.defaultProps = {
  editComment: null,
  editAttachments: null,
  onCancel: () => {},
  commentId: null,
};

export default withTheme(injectStyle(styles)(CommentInput));
