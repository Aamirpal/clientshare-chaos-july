import React, { useContext } from 'react';
import PropTypes from 'prop-types';
import get from 'lodash/get';
import isEmpty from 'lodash/isEmpty';
import take from 'lodash/take';
import MediaQuery from 'react-responsive';
import { UsersContext } from '../../utils/contexts';
import { Heading } from '../index';

const LikeBox = React.memo(({
  post, classes, showUserLikes, onUserClick,
}) => {
  const usersContext = useContext(UsersContext);
  const { users } = usersContext;
  const foundLength = get(post, 'endorse_count', 0) - 2;
  const takeEndrose = take(get(post, 'endorse', []), get(post, 'endorse_by_me', []).length ? 1 : 2);
  return (
    <>
      {(get(post, 'endorse', []).length || get(post, 'endorse_by_me', []).length) && !isEmpty(users) ? (
        <div className={classes.likeInner}>
          {get(post, 'endorse_by_me', []).length ? (
            <Heading as="h4" headingProps={{ className: classes.otherMembers }}>
                     You
              {get(post, 'endorse', []).length
                ? (
                  <span>
                    {(get(post, 'endorse_count', 0) === 2 ? <span>&nbsp;&</span> : ',')}
                    &nbsp;
                  </span>
                )
                : null}
            </Heading>
          ) : null}

          <MediaQuery query="(min-device-width: 767px)">
            {takeEndrose.map((endorse, index) => (
              <Heading as="h4" headingProps={{ className: classes.otherMembers, onClick: () => onUserClick(endorse.user_id) }} key={index}>
                {get(users[endorse.user_id], 'user.fullname')}
                {index !== takeEndrose.length - 1
                  ? <span>,&nbsp;</span>
                  : null}
              </Heading>
            ))}
          </MediaQuery>
          <MediaQuery query="(max-device-width: 767px)">
            {Boolean(get(post, 'endorse', []).length && !get(post, 'endorse_by_me', []).length) && (
              <Heading as="h4" headingProps={{ className: classes.otherMembers }}>
                {get(users[post.endorse[0].user_id], 'user.fullname')}
              </Heading>
            )}
          </MediaQuery>
          <MediaQuery query="(min-device-width: 767px)">
            {foundLength > 0 ? (
              <Heading as="h4" headingProps={{ className: classes.otherMembers, onClick: showUserLikes }}>
                          &nbsp;
                {`& ${foundLength} other${foundLength > 1 ? 's' : ''}`}
              </Heading>
            ) : null }
          </MediaQuery>
          <MediaQuery query="(max-device-width: 767px)">
            {get(post, 'endorse_count', 0) - 1 > 0 ? (
              <Heading as="h4" headingProps={{ className: classes.otherMembers, onClick: showUserLikes }}>
                          &nbsp;
                {`${get(post, 'endorse_count', 0) - 1} other${get(post, 'endorse_count', 0) - 1 > 1 ? 's' : ''}`}
              </Heading>
            ) : null }
          </MediaQuery>
          <Heading as="h4" headingProps={{ className: classes.usefulText }}> &nbsp; found this useful </Heading>
        </div>
      ) : null}
    </>
  );
});

LikeBox.propTypes = {
  post: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  showUserLikes: PropTypes.func.isRequired,
  onUserClick: PropTypes.func.isRequired,
};

export default LikeBox;
