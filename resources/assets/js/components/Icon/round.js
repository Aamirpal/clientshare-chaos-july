import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import injectSheet from 'react-jss';
import Icon from './index';
import withTheme from '../../utils/hoc/withTheme';


const styles = {
  roundIcon: {
    background: ({ theme }) => theme.light_green,
    borderRadius: 20,
    padding: 7,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    cursor: 'pointer',
  },
}

const RoundIcon = ({ icon, iconProps, classes, onClick }) => (
  <div className={classnames(classes.roundIcon, 'cross-icon')} onClick={onClick}>
    <Icon path={icon} iconProps={iconProps} />
  </div>
);

RoundIcon.defaultProps = {
  iconProps: {},
  path: '',
  onClick: () => {},
};
export default withTheme(injectSheet(styles)(RoundIcon));
