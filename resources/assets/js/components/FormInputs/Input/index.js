import React from 'react';
import PropTypes from 'prop-types';
import cs from 'classnames';
import injectSheet from 'react-jss';
import withTheme from '../../../utils/hoc/withTheme';

const styles = {
  container: {
    display: 'flex',
    flexDirection: 'column',
    position: 'relative',
    flexGrow: 1,
  },
  input: {
    border: 'none',
    '&::placeholder': {
      color: ({ theme }) => theme.light_gray,
      opacity: '1',
    },
    '&:disabled': {
      background: ({ theme }) => theme.white_color,
    },
    '&:focus': {
      outline: 'none',
    },
  },
  error: {
    fontSize: '12px',
    lineHeight: '16px',
    color: ({ theme }) => theme.alert_color,
    bottom: 12,
    position: ({ theme }) => theme.relative,
    zIndex: 9,
    left: 0,
  },
};
const Input = ({ inputProps, classes, error }) => (
  <div className={classes.container}>
    <input
      autoComplete="off"
      {...inputProps}
      className={cs(classes.input,
        inputProps.className)}
    />
    {error && (
    <div className={cs(classes.error)}>{error}</div>
    )}

  </div>
);

Input.propTypes = {
  inputProps: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  error: PropTypes.string,
};

Input.defaultProps = {
  error: '',
};

export default withTheme(injectSheet(styles)(Input));
