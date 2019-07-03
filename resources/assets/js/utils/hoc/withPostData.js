import React from 'react';
import { PostsFeedContext } from '../contexts';

const withPostData = (WrappedComponent) => {
  const postDataComponent = props => (
    <PostsFeedContext.Consumer>
      {({ postData }) => (<WrappedComponent postData={postData} {...props} />
      )}
    </PostsFeedContext.Consumer>
  );

  return postDataComponent;
};

export default withPostData;
