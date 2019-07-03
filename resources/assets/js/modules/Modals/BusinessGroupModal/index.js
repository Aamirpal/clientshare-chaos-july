import React, { useContext } from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import MediaQuery from 'react-responsive';
import _values from 'lodash/values';

import { GroupContext } from '../../../utils/contexts';
import { getItem } from '../../../utils/methods';
import withTheme from '../../../utils/hoc/withTheme';
import {
  Modal, Breadcrumb, Heading,
} from '../../../components';

import { styles } from '../styles';

const SelectGroupModal = React.memo(({
  modelProps, classes, onSelect, active, editReview,
}) => {
  const groups = useContext(GroupContext);

  return (
    <Modal
      modelProps={{ ...modelProps, dialogClassName: classes.category_popup }}
      headerText={(
        <>
          <MediaQuery query="(min-device-width: 767px)">
            <Breadcrumb items={['Group', 'Review']} active={0} />
          </MediaQuery>
          <MediaQuery query="(max-device-width: 767px)">
            <div>
              {!editReview ? 'Log a review' : 'Edit a review'}
            </div>
          </MediaQuery>
        </>
    )}
    >
      <div className={classes.container}>
        <MediaQuery query="(min-device-width: 767px)">
          <Heading as="h5" headingProps={{ className: classes.message }}>Who should see this? Choose a group:</Heading>
        </MediaQuery>

        <MediaQuery query="(max-device-width: 767px)">
          <div className="category-mbl-heading">
            <Heading as="h5" headingProps={{ className: classes.message }}>Who should see this?</Heading>
            <Heading headingProps={{ className: classes.message }}>Choose a group:</Heading>
          </div>
        </MediaQuery>
        <div className={classes.groupsContainer}>
          {_values(groups).map(group => (
            <div
              className={classnames(classes.groupTile, 'post-group-tile', {
                [classes.focusGroup]: !active && (Number(getItem('group')) === group.id),
                [classes.selectedGroup]: active === group.id,
              })}
              key={group.id}
              onClick={() => onSelect(group)}
            >
              <Heading as="h5" headingProps={{ className: classes.groupHeading }}>{group.name}</Heading>
              <p className={classes.memberCount}>
                <span className={classes.memberCountMain}>
                  {group.is_default ? 'all' : `${group.group_users_count} member${group.group_users_count > 1 ? 's' : ''}`}
                </span>
                <span className={classnames(`${group.is_default ? 'globe-icon' : 'post-lock-icon'}`, {
                  [classes.lockIcon]: !group.is_default,
                  [classes.globeIcon]: group.is_default,
                })}
                />
              </p>
            </div>
          ))}
        </div>
      </div>
    </Modal>
  );
});

SelectGroupModal.propTypes = {
  modelProps: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  onSelect: PropTypes.func.isRequired,
  active: PropTypes.any,
  editReview: PropTypes.any,
};

SelectGroupModal.defaultProps = {
  active: null,
  editReview: null,
};

export default withTheme(injectSheet(styles)(SelectGroupModal));
