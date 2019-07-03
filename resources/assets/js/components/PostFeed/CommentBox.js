import React from 'react';
import PropTypes from 'prop-types';
import get from 'lodash/get';
import classnames from 'classnames';

import './mention.css';
import { UsersContext, PostsFeedContext } from '../../utils/contexts';
import { globalConstants } from '../../utils/constants';
import {
  Image, ConfirmationModal,
} from '../index';
import CommentTiles from './CommentTiles';
import CommentInput from '../FormInputs/CommentInput';
import { deleteCommentApi } from '../../api/app';

const { userId } = globalConstants;


class MentionEditor extends React.PureComponent {
  static contextType = PostsFeedContext;

  static propTypes = {
    classes: PropTypes.object.isRequired,
    postId: PropTypes.string.isRequired,
  }

  mentionRef = React.createRef();

  state = {
    deleteModal: null,
  };

    deleteComment = () => {
      const { deleteModal } = this.state;
      const { postId } = this.props;
      const { postData: { posts }, updatePosts } = this.context;
      deleteCommentApi(get(deleteModal, 'id', null)).then(() => {
        const findIndex = get(posts[postId], 'comments', []).findIndex(comm => comm.id === deleteModal.id);
        posts[postId].comments.splice(findIndex, 1);
        updatePosts(posts);
        this.setState({ deleteModal: null });
      }).catch(() => false);
    }

    render() {
      const { deleteModal } = this.state;
      const { classes, postId } = this.props;
      return (
        <UsersContext.Consumer>
          {({ users }) => (
            <div className={classnames(classes.commentContainer, 'post-comment-container')}>
              <CommentTiles postId={postId} classes={classes} onDelete={comm => this.setState({ deleteModal: comm })} />
              <div className={classes.addCommentContainer}>
                <Image img={get(get(users, userId), 'user.circular_image_url')} size="img36" extraClass={classes.commentAvatar} />
                <CommentInput postId={postId} buttonText="Add a comment" />
              </div>
              <ConfirmationModal
                message="Do you want to permanently delete this comment?"
                headerText="Delete comment"
                buttonCancel="Cancel"
                modelProps={{ show: !!deleteModal, className: 'sm-popup', onHide: () => this.setState({ deleteModal: null }) }}
                onSuccess={this.deleteComment}
                onCancel={() => this.setState({ deleteModal: null })}
                buttonText="Delete comment"
              />
            </div>
          )}

        </UsersContext.Consumer>
      );
    }
}

export default MentionEditor;
