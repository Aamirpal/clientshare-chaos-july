import React, { useState } from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';

import withTheme from '../../../utils/hoc/withTheme';
import {
  Modal, Breadcrumb, AddPostForm, Heading, Button,
} from '../../../components';

import { styles } from '../styles';

const InsertPostModal = React.memo(({
  modelProps, classes, category, group, onGroupClick, onCategoryClick, formProps,
}) => {
  const [memberList, showMemberList] = useState(false);

  return (
    <Modal
      modelProps={{ ...modelProps, dialogClassName: classes.addPostPopup }}
      headerText={(
        <Breadcrumb items={['Category', 'Group', 'Post']} active={2} />
      )}
    >
      <div>
        <AddPostForm formProps={formProps} />
        <div className={classes.postBottom}>
          <div className={classes.bottomButtonPanel}>
            <Heading as="h5" headingProps={{ className: classes.bottomText }}>Who will see this post?</Heading>
            <Button iconProps={{ className: classes.categoryIcon }} icon={`/${category.category_logo}`} buttonProps={{ variant: 'secondary', className: classes.categoryButton, onClick: onCategoryClick }}>
              {category.category_name}
            </Button>
            <Button buttonProps={{ variant: 'primary_light', className: classes.groupButton, onClick: onGroupClick }}>
              {group.name}
            </Button>
            {group.id !== 'all' && (
              <Heading as="h5" headingProps={{ className: classes.toggleMember, onClick: () => showMemberList(!memberList) }}>
                {memberList ? 'Hide ' : 'Show '}
                member list
              </Heading>
            )}
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

      </div>
    </Modal>
  );
});

InsertPostModal.propTypes = {
  modelProps: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  category: PropTypes.object.isRequired,
  group: PropTypes.object.isRequired,
  onGroupClick: PropTypes.func.isRequired,
  onCategoryClick: PropTypes.func.isRequired,
  formProps: PropTypes.object.isRequired,
};

export default withTheme(injectSheet(styles)(InsertPostModal));
