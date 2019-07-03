import React from 'react';
import OverlayTrigger from 'react-bootstrap/OverlayTrigger';
import BaseTooltip from 'react-bootstrap/Tooltip';
import MediaQuery from 'react-responsive';
import './style.scss';

const Tooltip = ({ title, children, position }) => (
  <>
    <MediaQuery query="(min-device-width: 767px)">
      <OverlayTrigger
        placement={position}
        overlay={(
          <BaseTooltip>
            {title}
          </BaseTooltip>
)}
      >
        {children}
      </OverlayTrigger>
    </MediaQuery>
    <MediaQuery query="(max-device-width: 767px)">
      {children}
    </MediaQuery>
  </>
);

Tooltip.defaultProps = {
  position: 'top',
};

export default Tooltip;
