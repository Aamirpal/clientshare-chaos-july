import React from 'react';
import Proptypes from 'prop-types';

const Wrapper = ({ children }) => (
  <div className="categories-wrap">
    <div className="category-container" />
    {children}
  </div>
);

Wrapper.propTypes = {
  children: Proptypes.node.isRequired,
};

export default Wrapper;
