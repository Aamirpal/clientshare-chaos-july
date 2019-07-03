import React from 'react';
import BaseSpinner from 'react-bootstrap/Spinner';
import injectSheet from 'react-jss';

const styles = {
  loading: {
    position: ({ fixed }) => (fixed ? 'fixed' : 'absolute'),
    background: 'rgba(255, 255, 255, 0.8)',
    width: '100%',
    top: 0,
    left: 0,
    height: '100%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 999999,
  },
};
const Spinner = ({classes}) => (
  <div className={classes.loading}>
    <BaseSpinner animation="border" variant="success" />
  </div>
);

Spinner.defaultProps = {
  fixed: false,
};

export default injectSheet(styles)(Spinner);
