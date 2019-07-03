import React, { useContext } from 'react';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import _values from 'lodash/values';
import MediaQuery from 'react-responsive';

import { CategoryContext } from '../../../utils/contexts/index';
import withTheme from '../../../utils/hoc/withTheme';
import { getItem } from '../../../utils/methods';
import {
  Modal, Breadcrumb, Heading, Icon,
} from '../../../components';
import { styles } from '../styles';

const SelectCategoryModal = ({
  modelProps, classes, onSelect, active, editPost,
}) => {
  const categories = useContext(CategoryContext);
  return (
    <Modal
      modelProps={{ ...modelProps, dialogClassName: classes.category_popup }}
      headerText={(
        <>
          <MediaQuery query="(min-device-width: 767px)">
            {!editPost ? (
              <Breadcrumb items={['Category', 'Group', 'Post']} active={0} />
            ) : 'Edit a Category'}
          </MediaQuery>
          <MediaQuery query="(max-device-width: 767px)">
            <div>
              {!editPost ? 'Create a post' : 'Edit a post'}
            </div>
          </MediaQuery>
        </>
)}
    >
      <div className={classes.container}>
        <MediaQuery query="(min-device-width: 767px)">
          <Heading as="h5" headingProps={{ className: classes.message }}>Where do you want to post? Choose a category:</Heading>
        </MediaQuery>

        <MediaQuery query="(max-device-width: 767px)">
          <div className="category-mbl-heading">
            <Heading as="h5" headingProps={{ className: classes.message }}>Where do you want to post?</Heading>
            <Heading headingProps={{ className: classes.message }}>Choose a category:</Heading>
          </div>
        </MediaQuery>

        <div className={classes.categoryContainer}>
          {_values(categories).map(category => (
            category.category_name !== 'Business Reviews' && (
            <div
              onClick={() => onSelect(category)}
              key={category.category_id}
              className={classnames(classes.singleTileContainer, {
                [classes.focus]: !active && Number(getItem('category')) === category.category_id,
                [classes.activeCategory]: active === category.category_id,
              }, 'single-tile-container')}
            >
              <Icon path={`/${category.category_logo}`} iconProps={{ className: classes.catIcon }} />
              <Heading as="h5" headingProps={{ className: classes.title }}>{category.category_name}</Heading>
            </div>
            )
          ))}
        </div>
      </div>
    </Modal>
  );
};

SelectCategoryModal.propTypes = {
  modelProps: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  onSelect: PropTypes.func.isRequired,
  active: PropTypes.any,
  editPost: PropTypes.any,
};

SelectCategoryModal.defaultProps = {
  active: null,
  editPost: null,
};


export default withTheme(injectSheet(styles)(SelectCategoryModal));
