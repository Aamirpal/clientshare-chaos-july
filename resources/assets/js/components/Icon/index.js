import React from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import classnames from 'classnames';

const styles = {
  icon_image: {
    height: 'auto',
    width: 'auto',
  },
};

const Icon = React.memo(({ classes, path, iconProps }) => (
  <img src={path} alt="Icon" className={classnames(classes.icon_image, iconProps.className)} />
));

Icon.propTypes = {
  classes: PropTypes.object.isRequired,
  path: PropTypes.any.isRequired,
  iconProps: PropTypes.object,
};

Icon.defaultProps = {
  iconProps: {},
};


export default injectSheet(styles)(Icon);
