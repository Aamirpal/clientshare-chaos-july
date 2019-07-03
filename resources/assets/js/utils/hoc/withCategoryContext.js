import React from 'react';
import { UpdateCategoryContext, UpdateGroupContext } from '../contexts';

const withCategoryContext = (WrappedComponent) => {
  const CategoryData = props => (
    <UpdateCategoryContext.Consumer>
      {category => (
        <UpdateGroupContext.Consumer>
          {group => <WrappedComponent category={category} group={group} {...props} />}
        </UpdateGroupContext.Consumer>
      )}
    </UpdateCategoryContext.Consumer>
  );

  return CategoryData;
};

export default withCategoryContext;
