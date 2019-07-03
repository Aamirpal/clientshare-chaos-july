import React from 'react';
import PropTypes from 'prop-types';
import BaseButton from 'react-bootstrap/Button';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import withTheme from '../../utils/hoc/withTheme';
import Icon from '../Icon';

const styles = {
  icon: {
    marginRight: 8,
  },
};

const Button = ({
  children, buttonProps, classes, active, icon, iconProps,
}) => (
  <BaseButton
    type="button"
    variant="primary"
    className={classnames(classes.btn_group)}
    {...buttonProps}
    active={active}
  >
    {icon && <span className={classes.icon}><Icon path={icon} iconProps={iconProps} /></span>}
    {children}
  </BaseButton>
);

Button.propTypes = {
  children: PropTypes.node.isRequired,
  buttonProps: PropTypes.object,
  classes: PropTypes.object.isRequired,
  active: PropTypes.bool,
  icon: PropTypes.any,
  iconProps: PropTypes.object,
};

Button.defaultProps = {
  buttonProps: {},
  active: false,
  icon: null,
  iconProps: {},
};

export default withTheme(injectSheet(styles)(Button));
