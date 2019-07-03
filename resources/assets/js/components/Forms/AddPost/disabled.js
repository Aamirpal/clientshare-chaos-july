import React from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import MediaQuery from 'react-responsive';
import withTheme from '../../../utils/hoc/withTheme';
import { globalConstants } from '../../../utils/constants';
import { ImgGalary, ImgVideo, ImgAttachment } from '../../../images';
import { Image, Button } from '../../index';
import { styles } from './styles';

const { userImg } = globalConstants;
const AddPostDisabled = React.memo(({
  classes, disabled, onClick, attachBox,
}) => (
  <>
    <div id="add_post_trigger" className={classnames(classes.container, 'disable-container')} onClick={onClick}>
      <div className={classes.disabledTopPanel}>
        <Image img={userImg} size="img66" />
        <div className={classes.inputContainer}>
          <input disabled={disabled} placeholder={!attachBox ? 'Click here to log a review' : 'What do you want to talk about?'} className={classes.postInput} />
        </div>
        <div className="attach-col text-center flex-column hidden-desktop">
          <span className="attach-icon" />
          File
        </div>

        <Button>Post</Button>
      </div>
      <MediaQuery query="(min-device-width: 767px)">
        {attachBox && (
          <div className={classes.bottomPanel}>
            <Button icon={ImgGalary} buttonProps={{ variant: 'light', className: classes.button }} rounded> Images </Button>
            <Button icon={ImgVideo} buttonProps={{ variant: 'light', className: classes.button }} rounded> Videos </Button>
            <Button icon={ImgAttachment} buttonProps={{ variant: 'light', className: classes.button }} rounded> Files </Button>
          </div>
        )}
      </MediaQuery>
    </div>
  </>
));

AddPostDisabled.propTypes = {
  classes: PropTypes.object.isRequired,
  disabled: PropTypes.bool,
  onClick: PropTypes.func,
  attachBox: PropTypes.bool,
};

AddPostDisabled.defaultProps = {
  disabled: true,
  attachBox: true,
  onClick: () => {},
};

export default withTheme(injectSheet(styles)(AddPostDisabled));
