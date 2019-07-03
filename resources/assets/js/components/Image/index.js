import React from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import Img from 'react-image';
import userPlaceholder from '../../images/user-placeholder.svg';

import './image.scss';

const styles = {
  small_image: {
    height: 31,
    width: 31,
  },
  medium_image: {
    height: 48,
    width: 48,
  },
  img66: {
    height: 60,
    width: 60,
    '@media (max-width: 767px)': {
      height: 36,
      width: 36,
    },
  },
  margin_right: {
    marginRight: 5,
  },
  large_image: {
    height: 60,
    width: 60,
    '@media (max-width: 767px)': {
      width: 37,
      height: 37,
    },
    '@media (max-width: 481px)': {
      width: 39,
      height: 37,
    },
  },
  extra_large_image: {
    height: 200,
    width: 200,
  },
  img131: {
    height: 131,
    width: 131,
  },
  img36: {
    height: 36,
    width: 36,
    '@media (max-width: 767px)': {
      width: 37,
      height: 37,
    },
    '@media (max-width: 481px)': {
      width: 40,
      height: 36,
    },
  },
  img31: {
    width: 31.25,
    height: 40.62,
  },
  round_image: {
    borderRadius: '50%',
  },
  img: {
    height: '100%',
    width: '100%',
  },
  center: {
    textAlign: 'center',
  },
};

const Image = ({
  img, classes, size, round, extraClass, loadingClass, position,
}) => (
  <div className={classnames({
    [classes.small_image]: size === 'small',
    [classes.margin_right]: size === 'small',
    [classes.large_image]: size === 'large',
    [classes.extra_large_image]: size === 'extra_large',
    [classes.medium_image]: size === 'medium',
    [classes.img66]: size === 'img66',
    [classes.img131]: size === 'img131',
    [classes.img36]: size === 'img36',
    [classes.img31]: size === 'img31',
    [classes.center]: position === 'center',
  })}
  >
    <Img
      decode={false}
      src={img || userPlaceholder}
      loader={<div className={loadingClass || 'cummunity-small-avtar'} />}
      className={classnames(classes.img, extraClass, {
        [classes.round_image]: round,
      })}
    />
  </div>
);

Image.propTypes = {
  classes: PropTypes.object.isRequired,
  size: PropTypes.string,
  img: PropTypes.any,
  round: PropTypes.bool,
  extraClass: PropTypes.string,
  loadingClass: PropTypes.string,
  position: PropTypes.string,
};

Image.defaultProps = {
  size: 'large',
  img: null,
  round: true,
  extraClass: '',
  loadingClass: null,
  position: '',
};

export default injectSheet(styles)(Image);
