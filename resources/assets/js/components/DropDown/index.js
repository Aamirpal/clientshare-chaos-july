import React from 'react';
import PropTypes from 'prop-types';
import injectStyle from 'react-jss';
import DropdownMenu from 'react-bootstrap/DropdownMenu';
import DropdownItem from 'react-bootstrap/DropdownItem';
import cx from 'classnames';
import withTheme from '../../utils/hoc/withTheme';

import Icon from '../Icon';

const styles = {
  post_dropDown: {
    padding: 0,
    margin: 0,
    borderRadius: 10,
    minWidth: 168,
    top: '34px !important',
    left: '-29px !important',
    zIndex: 1,
    transform: 'none !important',
    border: ({ theme }) => `1px solid ${theme.dark_white}`,
    boxShadow: ({ theme }) => theme.shadow,
    '&:before': {
      top: '-8px',
      left: 'auto',
      right: 18,
      width: 16,
      height: 16,
      content: '""',
      zIndex: '-1',
      position: 'absolute',
      display: 'block',
      background: '#FFFFFF',
      transform: 'rotate(-135deg)',
      border: '1px solid #E8F0F8',
      '@media (max-width: 767px)': {
        right: 23,
      },
    },
    '@media (max-width: 767px)': {
      top: '37px !important',
      left: '0 !important',
      width: '98%',
      minWidth: '98%',
      maxWidth: '98%',
      margin: '0 auto',
      right: '0 !important',
    },
  },
  dropdownItem: {
    padding: '16px 17px',
    cursor: 'pointer',
    borderBottom: ({ theme }) => `1px solid ${theme.dark_white}`,
    background: ({ theme }) => theme.white_color,
    '&:hover': {
      background: ({ theme }) => theme.ghost_white,
    },
    '&:last-child': {
      border: 'none',
      borderBottomLeftRadius: 10,
      borderBottomRightRadius: 10,
      '&:hover': {
        borderBottomLeftRadius: 10,
        borderBottomRightRadius: 10,
      },
    },
    '&:first-child': {
      borderTopLeftRadius: 10,
      borderTopRightRadius: 10,
      '&:hover': {
        borderTopLeftRadius: 10,
        borderTopRightRadius: 10,
      },
    },
  },
  itemText: {
    fontSize: 14,
    lineHeight: '16px',
    color: ({ theme }) => theme.basic_color,
    marginLeft: 20,
  },
  itemHighlight: {
    color: ({ theme }) => theme.alert_color,
  },
  itemIcon: {
    maxWidth: 16,
    maxHeight: 16,
  },
};

const DropDown = React.memo(({
  items, classes, onClick, condition, hide,
}) => (
  <DropdownMenu className={classes.post_dropDown}>
    {items.map((item, index) => (!hide.includes(index) || !condition) && (
      <DropdownItem
        as="div"
        className={classes.dropdownItem}
        key={item.key}
        onClick={() => onClick(item)}
      >
        <Icon path={item.icon} iconProps={{ className: classes.itemIcon }} />
        <span className={cx(classes.itemText,
          {
            [classes.itemHighlight]: item.highlight,
          })}
        >
          {item.name}
        </span>
      </DropdownItem>

    ))}
  </DropdownMenu>
));

DropDown.propTypes = {
  items: PropTypes.array.isRequired,
  classes: PropTypes.object.isRequired,
  onClick: PropTypes.func,
  condition: PropTypes.bool,
  hide: PropTypes.array,
};

DropDown.defaultProps = {
  onClick: () => {},
  condition: false,
  hide: [],
};

export default withTheme(injectStyle(styles)(DropDown));
