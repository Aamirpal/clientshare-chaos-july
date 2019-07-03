import React from 'react';
import ContentLoad from 'react-content-loader';
import classnames from 'classnames';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import { isIEBrower } from '../../utils/methods';

const styles = {
  loader: {
    display: 'flex',
  },
};

const ContentLoader = ({
  items, height, className, classes,
}) => {
  const loaders = [];
  for (let i = 0; i < items; i += 1) {
    loaders.push(
      <div className={className} key={i}>
        <div className={classnames('loader-flex', {
          [classes.loader]: isIEBrower(),
        })}
        >
          <ContentLoad height={height} animate={false}>
            <circle cx="60" cy="60" r="43" />
            <rect x="122" y="38" rx="0" ry="0" width="246" height="15" />
            <rect x="124" y="65" rx="0" ry="0" width="182" height="15" />
            <rect x="28" y="124" rx="0" ry="0" width="281" height="18" />
            <rect x="28" y="158" rx="0" ry="0" width="281" height="18" />
          </ContentLoad>
        </div>
      </div>,
    );
  }

  return (
    <>
      {loaders}
    </>
  );
};

ContentLoader.propTypes = {
  items: PropTypes.number,
  height: PropTypes.number,
  className: PropTypes.string,
  classes: PropTypes.object.isRequired,
};

ContentLoader.defaultProps = {
  items: 1,
  height: 200,
  className: '',
};


export default injectSheet(styles)(ContentLoader);
