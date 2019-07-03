import React from 'react';
import { CategoryContext, GroupContext } from '../contexts';

const withData = (WrappedComponent) => {
  const CategoryData = props => (
    <CategoryContext.Consumer>
      {category => (
        <GroupContext.Consumer>
          {group => <WrappedComponent categories={category} groups={group} {...props} />}
        </GroupContext.Consumer>
      )}
    </CategoryContext.Consumer>
  );

  return CategoryData;
};

export default withData;
