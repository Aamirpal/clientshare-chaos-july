import React, { useEffect } from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import BaseTextarea from 'react-textarea-autosize';
import cx from 'classnames';
import withTheme from '../../../utils/hoc/withTheme';

const styles = {
  container: {
    display: 'flex',
    position: 'relative',
    flexDirection: 'column',
  },
  error: {
    fontSize: '12px',
    lineHeight: '16px',
    color: ({ theme }) => theme.alert_color,
    top: 0,
    position: ({ theme }) => theme.relative,
    zIndex: 9,
    left: 0,
  },
};
const TextArea = React.memo(({
  inputProps, classes, error, errorClass,
}) => (
  <div className={classes.container}>
    <BaseTextarea
      {...inputProps}
    />
    {error && (
      <div className={cx(classes.error, errorClass)}>{error}</div>
    )}

  </div>
));

TextArea.propTypes = {
  inputProps: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  error: PropTypes.string,
  errorClass: PropTypes.string,
};

TextArea.defaultProps = {
  error: '',
  errorClass: '',
};

export default withTheme(injectSheet(styles)(TextArea));
