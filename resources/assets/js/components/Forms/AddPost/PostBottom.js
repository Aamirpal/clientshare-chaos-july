import React from 'react';
import PropTypes from 'prop-types';
import get from 'lodash/get';
import cx from 'classnames';
import injectSheet from 'react-jss';
import MediaQuery from 'react-responsive';
import withTheme from '../../../utils/hoc/withTheme';
import { Tooltip, Heading, Button } from '../../index';
import { styles } from './styles';

const PostBottom = React.memo(({
  classes, category, group, onGroupClick, onCategoryClick, memberList,
  showMemberList, seePost, seeText, seeMore,
}) => (
  <div className={classes.postBottom}>
    <div className={classes.bottomButtonPanel}>
      <Heading as="h5" headingProps={{ className: classes.bottomText }}>{seePost}</Heading>
      <div className="d-flex post-action-mobile">
        {get(category, 'category_name', null) && (
          <Tooltip title={<div className={classes.buttonTooltip}>Click here to change the category</div>}>
            <div>
              <Button iconProps={{ className: classes.categoryIcon }} icon={`/${category.category_logo}`} buttonProps={{ variant: 'secondary', className: classes.categoryButton, onClick: onCategoryClick }}>
                {category.category_name}
              </Button>
            </div>
          </Tooltip>
        )}
        {get(group, 'name', null) && (
          <Tooltip title={<div className={classes.buttonTooltip}>Click here to change the group</div>}>
            <div>
              <Button buttonProps={{
                variant: 'primary_light',
                className: cx(classes.groupButton, {
                  [classes.showGroupButton]: memberList,
                }),
                onClick: onGroupClick,
              }}
              >
                {group.name}
              </Button>
            </div>
          </Tooltip>
        )}
        <MediaQuery query="(min-device-width: 767px)">
          {!group.is_default && (
            <Heading as="h5" headingProps={{ className: classes.toggleMember, onClick: () => showMemberList(!memberList) }}>
              {memberList ? 'Hide' : seeText}
                    &nbsp;member list
            </Heading>
          )}
        </MediaQuery>
        <MediaQuery query="(max-device-width: 767px)">
          {!group.is_default && (
            <Heading as="h5" headingProps={{ className: classes.toggleMember, onClick: () => showMemberList(!memberList) }}>
              {memberList ? 'Hide' : seeMore}
                    &nbsp;more
            </Heading>
          )}
        </MediaQuery>
      </div>
    </div>
    {(memberList && group.members) && (
      <div className={classes.memberContainer}>
        {group.members.map(member => (
          <div key={member.user_id} className={classes.memberTile}>
            {member.full_name}
          </div>
        ))}
      </div>
    )}
  </div>
));

PostBottom.propTypes = {
  classes: PropTypes.object.isRequired,
  category: PropTypes.object,
  group: PropTypes.object,
  onGroupClick: PropTypes.func,
  onCategoryClick: PropTypes.func,
  memberList: PropTypes.bool,
  showMemberList: PropTypes.func,
  seePost: PropTypes.string,
  seeText: PropTypes.string,
  seeMore: PropTypes.string,
};

PostBottom.defaultProps = {
  category: {},
  group: {},
  onGroupClick: () => {},
  onCategoryClick: () => {},
  memberList: false,
  showMemberList: () => {},
  seePost: 'Who will see this post?',
  seeText: 'Show',
  seeMore: 'See',
};

export default withTheme(injectSheet(styles)(PostBottom));
