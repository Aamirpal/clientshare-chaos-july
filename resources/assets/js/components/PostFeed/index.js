import React, { useState, useContext } from 'react';
import injectStyle from 'react-jss';
import PropTypes from 'prop-types';
import Dropdown from 'react-bootstrap/Dropdown';
import moment from 'moment';
import get from 'lodash/get';
import SweetAlert from 'sweetalert2-react';
import isEmpty from 'lodash/isEmpty';
import take from 'lodash/take';
import cx from 'classnames';
import MediaQuery from 'react-responsive';
import Parser from 'html-react-parser';
import reactStringReplace from 'react-string-replace';

import { UsersContext, UserInfoContext } from '../../utils/contexts';
import withData from '../../utils/hoc/withData';
import withTheme from '../../utils/hoc/withTheme';
import { globalConstants, API_URL } from '../../utils/constants';
import { copyLink } from '../../utils/methods';
import { dropdownItems } from '../../utils/helper';
import { LikeIcon } from '../Svg';
import {
  Image, Heading, Icon, AttachmentTile,
  UrlPreview, DocumentPreview, DropDownItems, ConfirmationModal,
} from '../index';
import CommentBox from './CommentBox';
import LikeBox from './LikeBox';
import {
  lockGray, moreIcon, eyeIcon, videoImg, pinWhite, GlobeImg, commentsIcon,
} from '../../images';
import { styles } from './styles';
import Tooltip from '../Tooltip';
import { UsersModal } from '../../modules/Modals';

const {
  clientShareId, isAdmin, userId,
} = globalConstants;

const PostFeed = React.memo(({
  classes, post, onDelete, onPinPost, onEditPost, groups,
  categories, theme, onEndorsePost,
}) => {
  const usersContext = useContext(UsersContext);
  const images = get(post, 'images');
  const videos = get(post, 'videos');
  const allAttachments = [...get(post, 'images', []), ...get(post, 'videos', []), ...get(post, 'documents', [])];

  const [preview, showPreview] = useState(false);
  const [userViews, showUserViews] = useState(false);
  const [userLikes, showUserLikes] = useState(false);
  const isPinned = get(post, 'pin_status', false);
  const [deleteModal, setDeleteModal] = useState(false);
  const [alert, showAlert] = useState(false);
  const { updateUserInfo } = useContext(UserInfoContext);

  const doAction = ({ key }) => {
    switch (key) {
      case 'copy':
        showAlert(true);
        setTimeout(() => showAlert(false), 1500);
        return copyLink(`${API_URL}/clientshare/${clientShareId}/${get(post, 'id', null)}`);
      case 'pin':
        return onPinPost(post);
      case 'delete':
        return setDeleteModal(true);
      case 'edit':
        return onEditPost();
      default:
        return false;
    }
  };

  if (isPinned) {
    dropdownItems[0].name = 'Unpin Post';
  } else {
    dropdownItems[0].name = ' Pin Post';
  }
  const isEveryone = get(get(groups, `'${get(post, 'group_id')}'`), 'is_default', null);
  const allViews = get(post, 'post_view', []);

  const { users } = usersContext;
  const openUserPopup = (id = null) => {
    updateUserInfo(id, post);
  };
  return (
    <div className={cx(classes.mainContainer, 'single-mbl-post', {
      [classes.pinFeedContainer]: isPinned,
    })}
    >
      <div className={classes.container}>
        <div className={classes.topContainer}>
          <div onClick={() => openUserPopup()} className={classes.pointer}>
            <Image img={get(post, 'user.circular_image_url', null)} />
          </div>
          <div className={classes.midTextContainer}>
            <div className={classes.nameContainer}>
              <Heading as="h4" headingProps={{ className: cx(classes.name, classes.pointer), onClick: () => openUserPopup() }}>{get(post, 'user.fullname', null)}</Heading>
              <Dropdown className={classes.parentDropdown}>
                <Dropdown.Toggle className={classes.postfeedDropdown} as="div">
                  <Icon path={moreIcon} />
                </Dropdown.Toggle>
                <DropDownItems onClick={doAction} items={dropdownItems} hide={[1, 2]} condition={!isAdmin && (userId !== get(post, 'user_id'))} />
              </Dropdown>
            </div>
            <div className={classes.bottomName}>
              <span className={classes.date}>
                {moment(get(post, 'created_at')).format('MMMM DD, HH:mm')}
                {get(post, 'created_at') !== get(post, 'updated_at') && (
                  <span>&nbsp;(edited)</span>
                )}
              </span>
              <span className="d-flex align-items-center">
                <Icon
                  path={isEveryone ? GlobeImg : lockGray}
                  iconProps={{ className: classes.lockIcon }}
                />
                {get(get(categories, post.space_category_id), 'category_name')}
              </span>
            </div>
          </div>
        </div>
        <div className={classes.bottomContainer}>
          <Heading as="h3" headingProps={{ className: classes.subject }}>{post.post_subject}</Heading>
          <div className={cx(classes.description, 'post-description-content')}>
            {Parser(get(post, 'post_description', ''), {
              replace: (domNode) => {
                if (/(((http|https|ftp|ftps)\:\/\/)|(www.))[a-zA-Z0-9\-\_\.]+\.[a-zA-Z]{2,3}(\S*)?/ig.test(get(domNode, 'data'))) {
                  const replacedText = reactStringReplace(get(domNode, 'data', ''), /(https?:\/\/\S+)/g, (match, i) => (
                    <a key={match + i} target="_blank" href={match}>{match}</a>
                  ));
                  return <span>{replacedText}</span>;
                }
              },
            })}
          </div>
        </div>
      </div>
      {(get(post, 'images', []).length || get(post, 'videos', []).length || get(post, 'documents', []).length) ? (
        <div className={classes.attachmentContainer}>
          {(get(post, 'images', []).length || get(post, 'videos', []).length) ? (
            <div className={classes.imageContainer}>
              {images && images.length ? (
                <div onClick={() => showPreview(true)}>
                  <Image position="center" img={images[0].post_file_url} round={false} size="auto" extraClass={classes.post_image} loadingClass={classes.post_image_load} />
                </div>
              ) : null}
              {images && (!images.length && videos.length)
                ? (
              // eslint-disable-next-line jsx-a11y/media-has-caption
                  <video width="100%" controls>
                    <source src={videos[0].post_file_url} type={videos[0].metadata.mimeType} />
                  Your browser does not support the video tag.
                  </video>
                ) : null}
            </div>
          ) : null}
          {Boolean(post.url_preview) && <UrlPreview embedData={post.url_preview} noImage />}
          <AttachmentTile
            files={(get(post, 'images', []).length || get(post, 'videos', []).length) ? allAttachments.slice(1) : allAttachments}
            icon={videoImg}
            isPreview
            showMore
          />
        </div>
      ) : null}
      {Boolean(post.url_preview && (!allAttachments.length)) && <UrlPreview embedData={post.url_preview} noImage />}
      <div className={classes.likeContainer}>
        <div className={classes.onlyFlex}>
          <div onClick={onEndorsePost} className={classes.likeButton}>
            <LikeIcon color={get(post, 'endorse_by_me', []).length ? theme.primary_color : theme.light_gray} />
          </div>
          <LikeBox
            classes={classes}
            post={post}
            showUserLikes={() => showUserLikes([
              ...post.endorse_by_me,
              ...post.endorse,
            ])}
            onUserClick={openUserPopup}
          />
        </div>
        <div className={classes.onlyFlex}>
          <div className={classes.onlyFlexInner}>
            <Icon path={commentsIcon} iconProps={{ className: classes.viewIcon }} />
            <Heading as="h4" headingProps={{ className: classes.commentsText }}>
              {post.comments.length}
              {' '}
              comment
              {`${post.comments.length > 1 ? 's' : ''}`}
            </Heading>
          </div>
          <Tooltip
            title={
            allViews.length && !isEmpty(users) ? (
              <div>
                {take(allViews, 5).map(view => (
                  <div key={view.user_id} className={classes.viewItem}>
                    {get(users[view.user_id], 'user.fullname', null)}
                  </div>
                ))}
                {Boolean(allViews.length) > 5 && (
                <div className={classes.viewItem}>
                  {`and ${allViews.length - 5} others`}
                </div>
                )}
              </div>
            ) : (
              <div className={classes.viewItem}>
            No Views
              </div>
            )
          }
            position="bottom"
          >
            <div className={cx(classes.onlyFlexInner, 'hidden-mbl-view')} onClick={() => showUserViews(allViews)}>
              <Icon path={eyeIcon} iconProps={{ className: classes.viewIcon }} />
              <Heading as="h4" headingProps={{ className: classes.viewsText }}>
                {`${allViews.length} views`}
              </Heading>
            </div>
          </Tooltip>
        </div>
      </div>
      <CommentBox classes={classes} postId={post.id} />
      {Boolean(preview)
      && <DocumentPreview modelProps={{ show: preview, onHide: () => showPreview(false) }} file={get(post, 'images', []).length ? post.images[0] : null} />}
      {Boolean(isPinned) && (
      <div className={classes.pinContainer}>
        <Icon path={pinWhite} iconProps={{ className: classes.pinIcon }} />
        <Heading headingProps={{ className: classes.pinText }}>Pinned post</Heading>
      </div>
      )}
      {deleteModal && (
      <ConfirmationModal
        message={(
          <MediaQuery minDeviceWidth={767}>
            {(matches) => {
              if (matches) {
                return 'Do you want to permanently delete this post?';
              }
              return 'Are you sure you want to delete this post?';
            }}
          </MediaQuery>
            )}
        headerText="Delete post"
        buttonCancel="Cancel"
        modelProps={{ show: deleteModal, className: 'sm-popup', onHide: () => setDeleteModal(false) }}
        onSuccess={() => onDelete(post)}
        onCancel={() => setDeleteModal(false)}
        buttonText="Delete post"
      />
      )}
      {userViews && (
      <UsersModal
        users={userViews}
        modalProps={{
          onHide: () => showUserViews(false),
          show: !!userViews,
          dialogClassName: classes.usersModal,
        }}
        title="Views"
      />
      )}
      {userLikes && (
      <UsersModal
        users={userLikes}
        modalProps={{
          onHide: () => showUserLikes(false),
          show: !!userLikes,
          dialogClassName: classes.usersModal,
          className: 'found-useful-modal',
        }}
        title="They found this post useful"
      />
      )}
      <MediaQuery query="(max-device-width: 767px)">
        <SweetAlert
          show={alert}
          onConfirm={() => showAlert(false)}
          type="success"
          title="Link copied!"
          showConfirmButton={false}
        />
      </MediaQuery>
    </div>
  );
});

PostFeed.propTypes = {
  classes: PropTypes.object.isRequired,
  post: PropTypes.object,
  onDelete: PropTypes.func,
  onPinPost: PropTypes.func,
  onEditPost: PropTypes.func,
  onEndorsePost: PropTypes.func,
  groups: PropTypes.object.isRequired,
  categories: PropTypes.object.isRequired,
  theme: PropTypes.object.isRequired,
};

PostFeed.defaultProps = {
  post: {},
  onDelete: () => {},
  onPinPost: () => {},
  onEditPost: () => {},
  onEndorsePost: () => {},
};

export default withData(withTheme(injectStyle(styles)(PostFeed)));
