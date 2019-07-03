import React from 'react';
import theme from '../../../sass/styles';

const withContext = (WrappedComponent) => {
  const Theme = props => (
    <WrappedComponent {...props} theme={theme.light} />
  );

  return Theme;
};

export default withContext;
