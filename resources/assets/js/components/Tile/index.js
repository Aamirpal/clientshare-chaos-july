import React from 'react';
import Proptypes from 'prop-types';

import './tile.scss';

const Tile = React.memo(({
  whitecolor, heading, children, withButton, members, goto,
}) => (
  <a href={goto}>
    <div className={`container-bg container-bg-${whitecolor}`}>
      <div className="box-equal">
        <div className="community-head">
          <h3>{heading}</h3>
          <p>
            {`${members} Members`}
          </p>
        </div>
        { withButton && children }
      </div>
    </div>
  </a>
));

Tile.propTypes = {
  whitecolor: Proptypes.string.isRequired,
  heading: Proptypes.string.isRequired,
  children: Proptypes.node.isRequired,
  withButton: Proptypes.bool,
  members: Proptypes.number,
  goto: Proptypes.string.isRequired,
};

Tile.defaultProps = {
  withButton: false,
  members: 0,
};

export default Tile;

export { default as MemberTile } from './MemberTile';
export { default as TileWrapper } from './TileWrapper';
