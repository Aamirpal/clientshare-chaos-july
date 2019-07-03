import React from 'react';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import { getItem } from '../../../utils/methods';
import { Icon, Heading } from '../../index';
import withTheme from '../../../utils/hoc/withTheme';

const styles = {
  container: {
    padding: '16px 14px',
    borderRadius: 10,
    width: '32%',
    display: 'flex',
    alignItems: 'flex-start',
    justifyContent: 'flex-start',
    marginBottom: 10,
    maxHeight: 64,
    cursor: 'pointer',
    boxSizing: 'border-box',
    color: ({ theme }) => theme.light_gray,
    '&:hover': {
      background: ({ theme }) => theme.white_color,
      border: ({ theme }) => `2px solid ${theme.primary_color}`,
      color: ({ theme }) => theme.basic_color,
    },
  },
  title: {
    fontSize: 14,
  },
  icon: {
    height: 14,
    width: 14,
    marginRight: 9,
  },
  focus: {
    background: ({ theme }) => theme.white_color,
    border: ({ theme }) => `2px solid ${theme.white_color}`,
    boxShadow: '8px 8px 14px rgba(190, 197, 214, 0.2), -8px -8px 14px rgba(190, 197, 214, 0.2)',
  },
  normal: {
    background: ({ theme }) => theme.dusky_gray,
    border: ({ theme }) => `2px solid ${theme.dusky_gray}`,
  },
};

const SmallCategoryTile = ({ category, classes }) => {
  const selectedCategory = (Number(getItem('category')) === category.category_id);
  return (
    <div className={classnames(classes.container, {
      [classes.normal]: !selectedCategory,
      [classes.focus]: selectedCategory,
    })}
    >
      <Icon path={`/${category.category_logo}`} iconProps={{ className: classes.icon }} />
      <Heading as="h5" headingProps={{ className: classes.title }}>{category.category_name}</Heading>
    </div>
  );
};

export default withTheme(injectSheet(styles)(SmallCategoryTile));
