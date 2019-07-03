import React from 'react';
import Proptypes from 'prop-types';

const Heading = ({ children, as, headingProps }) => {
  switch (as) {
    case 'h1':
      return <h1 {...headingProps}>{children}</h1>;
    case 'h2':
      return <h2 {...headingProps}>{children}</h2>;
    case 'h3':
      return <h3 {...headingProps}>{children}</h3>;
    case 'h4':
      return <h4 {...headingProps}>{children}</h4>;
    case 'h5':
      return <h5 {...headingProps}>{children}</h5>;
    case 'h6':
      return <h6 {...headingProps}>{children}</h6>;
    default:
      return <p {...headingProps}>{children}</p>;
  }
};

Heading.propTypes = {
  children: Proptypes.node,
  as: Proptypes.node,
  headingProps: Proptypes.object,
};

Heading.defaultProps = {
  as: 'p',
  headingProps: {},
  children: '',
};

export default Heading;
