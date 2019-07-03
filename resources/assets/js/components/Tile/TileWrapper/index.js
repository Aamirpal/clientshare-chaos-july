import React from 'react';
import injectSheet from 'react-jss';
import Card from 'react-bootstrap/Card';
import PropTypes from 'prop-types';
import withTheme from '../../../utils/hoc/withTheme';

const styles = {
  tileStyle: ({ theme, color }) => ({
    background: theme[[color]] || theme.basic_color,
    alignItems: theme.center,
    minHeight: 199,
    justifyContent: theme.center,
    borderRadius: 10,
    border: '1px solid rgba(96, 214, 181, 0.3)',
    width: theme.full_width,
  }),
};
const TileWrapper = ({ classes, children }) => (
  <div className="add-member-tile">
    <Card className={classes.tileStyle}>
      {children}
    </Card>
  </div>
);

TileWrapper.propTypes = {
  classes: PropTypes.object.isRequired,
  children: PropTypes.node.isRequired,
};

export default withTheme(injectSheet(styles)(TileWrapper));
