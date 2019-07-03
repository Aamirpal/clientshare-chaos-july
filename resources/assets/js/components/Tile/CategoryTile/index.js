import React from 'react';
import classnames from 'classnames';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import withTheme from '../../../utils/hoc/withTheme';
import Heading from '../../Heading';
import Icon from '../../Icon';
import styles from './styles';

const CategoryTile = React.memo(({
  category: {
    category_id,
    category_logo,
    category_name,
    last_posted_by,
    found_useful,
  }, onClick, onKeyPress, active, classes,
}) => (
  <div
    className={classnames(classes.tileContainer, {
      [classes.active]: active === category_id,
    })}
    onKeyPress={onKeyPress}
    onClick={onClick}
    role="button"
    tabIndex={category_id}
  >
    <Heading as="h3" headingProps={{ className: classes.topHeading }}>
      {category_logo && (
      <Icon path={`/${category_logo}`} iconProps={{ className: classes.icon }} />
      )}
      <span className={classes.categoryName}>{category_name}</span>
    </Heading>
    {last_posted_by && (
      <Heading as="h5" headingProps={{ className: classes.subHeading }}>
        Last updated by
        <span className={classes.highlightText}>{last_posted_by}</span>
      </Heading>
    )}
    {found_useful && (
      <Heading headingProps={{ className: classes.bottomText }}>
        {`${found_useful} found this post useful`}
      </Heading>
    )}

    {!last_posted_by && !found_useful && (
    <Heading headingProps={{ className: classes.bottomEmptyCategoryText }}>
       There are no posts in this category at the moment
    </Heading>
    )}
  </div>
));

CategoryTile.propTypes = {
  category: PropTypes.object.isRequired,
  onClick: PropTypes.func,
  onKeyPress: PropTypes.func,
  active: PropTypes.any,
  classes: PropTypes.object.isRequired,
};

CategoryTile.defaultProps = {
  onClick: () => {},
  onKeyPress: () => {},
  active: null,
};

export { default as SmallCategoryTile } from './SmallCategoryTile';

export default withTheme(injectSheet(styles)(CategoryTile));
