import React from 'react';
import Icon from '../../components/Icon';
import Heading from '../../components/Heading';
import globeIcon from '../../images/globe_icon_gray.svg';
import './edit-group.scss';

const Everyone = () => (
  <div className="gray-tile-row">
    <div className="group-tile">
      <Heading as="h5">Everyone</Heading>
      <p className="member-count">
        <span>all</span>
        <span className="icon">
          <Icon path={globeIcon} />
        </span>
      </p>
    </div>
  </div>
);

export default Everyone;
