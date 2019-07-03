import React from 'react';
import classnames from 'classnames';
import injectSheet from 'react-jss';
import withTheme from '../../utils/hoc/withTheme';
import Icon from '../Icon';
import { RightArrow, ActiveImg } from '../../images';

const styles = {
  item: {
    marginRight: 13,
    cursor: 'pointer',
    color: ({ theme }) => theme.light_gray,
    '&:last-child > span': {
      display: 'none',
    },
  },
  active: {
    color: ({ theme }) => theme.basic_color,
  },
  rightIcon: {
    marginLeft: 13,
  },
  tickIcon: {
    marginRight: 10,
    top: -1,
    position: 'relative',
  },
};

const Breadcrumb = ({
  items, classes, active, onClick,
}) => (
  <ul>
    {items.map((item, i) => (
      // eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
      <li
        key={i}
        onClick={() => onClick(i)}
        className={classnames(classes.item, {
          [classes.active]: i === active,
        })}
      >
        {i < active && (
        <span className={classnames(classes.tickIcon)}>
          <Icon path={ActiveImg} />
        </span>
        )}

        {item}
        <span className={classnames(classes.rightIcon)}>
          <Icon path={RightArrow} />
        </span>
      </li>
    ))}
  </ul>
);

Breadcrumb.defaultProps = {
  onClick: () => {},
};

export default withTheme(injectSheet(styles)(Breadcrumb));
