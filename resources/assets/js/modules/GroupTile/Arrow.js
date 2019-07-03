import React from 'react';
import PropTypes from 'prop-types';

const Arrow = ({ direction, styleClassName, clickHandler }) => (
  <div
    className={`slick-arrow slick-arrow-${direction} ${styleClassName}`}
    onClick={e => clickHandler(direction)}
    role="presentation"
  >
    {direction === 'left' && '<' || '>'}
  </div>
);

Arrow.propTypes = {
  styleClassName: PropTypes.string.isRequired,
  direction: PropTypes.string.isRequired,
  clickHandler: PropTypes.func.isRequired,
};

export default Arrow;
