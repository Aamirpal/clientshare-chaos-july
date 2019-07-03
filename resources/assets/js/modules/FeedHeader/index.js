import React from 'react';
import PropTypes from 'prop-types';

import Icon from '../../components/Icon';
import Heading from '../../components/Heading';
import './feed-header.scss';

const FeedHeader = ({ icon, text }) => (
  <div className="feed-header">
    <div className="feed-head-left">
      <Icon path={icon} />
      <Heading as="h2">{text}</Heading>
    </div>
  </div>
);

FeedHeader.propTypes = {
  icon: PropTypes.string.isRequired,
  text: PropTypes.string.isRequired,
};

export default FeedHeader;
