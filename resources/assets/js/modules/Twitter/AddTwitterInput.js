import React from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import Form from 'react-bootstrap/Form';
import withTheme from '../../utils/hoc/withTheme';
import Icon from '../../components/Icon';
import fieldCloseIcon from '../../images/close_bg_icon.svg';
import { styles } from './styles';

const AddTwitterInput = ({
  inputProps, classes, labelText, cancelInput, showApiError,
}) => (
  <div className="form-group">
    <label className="w-100">
      {labelText}
      <div className="d-flex w-100 position-relative">
        <Form.Control
          className={classes.tagInput}
          type="text"
          placeholder="@twitterhandle"
          autoComplete="off"
          spellCheck="false"
          {...inputProps}
        />
        <span className={classes.inputClose} onClick={cancelInput}>
          <Icon path={fieldCloseIcon} />
        </span>
      </div>
      {showApiError.twitter_handles && <div className={classes.errorMessage}>{showApiError.twitter_handles}</div>}
    </label>
  </div>
);

AddTwitterInput.propTypes = {
  classes: PropTypes.object.isRequired,
  labelText: PropTypes.number.isRequired,
  cancelInput: PropTypes.any.isRequired,
  inputProps: PropTypes.object.isRequired,
  showApiError: PropTypes.any,
};

AddTwitterInput.defaultProps = {
  showApiError: false,
};


export default withTheme(injectSheet(styles)(AddTwitterInput));
