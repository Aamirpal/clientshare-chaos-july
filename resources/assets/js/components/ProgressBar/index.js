import React from 'react';
import PropTypes from 'prop-types';
import BaseProgressBar from 'react-bootstrap/ProgressBar';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import RoundIcon from '../Icon/round';
import withTheme from '../../utils/hoc/withTheme';
import closeIcon from '../../images/close-icon.svg';

const styles = {
  progressBarContainer: {
    display: 'flex',
    padding: '5px 14px',
    alignItems: 'center',
    border: ({ theme }) => `1px solid ${theme.dark_white}`,
    marginBottom: 10,
    borderRadius: 18,
  },
  progress: {
    width: '100%',
    borderRadius: 18,
    background: 'none',
    height: 14,
  },
  innerProgress: {
    borderRadius: 18,
  },
  percentage: {
    marginRight: 7,
    fontSize: 10,
    lineHeight: 'normal',
    color: ({ theme }) => theme.basic_color,
  },
  cancelIcon: {
    width: 8,
  },
};

const ProgressBar = ({
  loaders, cancelRequest, classes, extraClass,
}) => (
  <>
    {loaders && loaders.map((loaderData, index) => (
      <div
        className={classnames(classes.progressBarContainer, extraClass)}
        key={index}
      >
        <div className={classes.percentage}>{`${loaderData.progress ? loaderData.progress : 0}%`}</div>
        <BaseProgressBar variant="primary" now={loaderData.progress} className={classes.progress} />
        <RoundIcon
          icon={closeIcon}
          iconProps={{ className: classes.cancelIcon }}
          onClick={() => cancelRequest(loaderData)}
        />
      </div>
    ))}
  </>
);

ProgressBar.propTypes = {
  loaders: PropTypes.any.isRequired,
  cancelRequest: PropTypes.func.isRequired,
  classes: PropTypes.object.isRequired,
  extraClass: PropTypes.any,
};

ProgressBar.defaultProps = {
  extraClass: '',
};

export default withTheme(injectSheet(styles)(ProgressBar));
