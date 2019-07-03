import React from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import Modal from './index';
import Icon from '../Icon';
import withTheme from '../../utils/hoc/withTheme';
import deleteIcon from '../../images/delete_icon.svg';
import { style } from './style';


const ConfirmationModal = ({
  onCancel, onSuccess, message, classes, modelProps, headerText, buttonCancel, changesRequest, buttonText,
}) => (
  <Modal headerText={headerText} modelProps={modelProps}>
    <div className={classes.modalContainer}>
      <div className={classes.topContainer}>
        <div className="edit-group-popup">
          <p className={classes.modalBodyHeading}>
            {message}
          </p>
          <div className={classnames(classes.confirmActionColumn, 'w-100 d-flex justify-content-end')}>
            <div
              onClick={onCancel}
              className={classnames(classes.transparentButton)}
            >
              {buttonCancel}
            </div>
            <div
              onClick={onSuccess}
              className={classnames(classes.redBtn, 'green-btn')}
            >
              <Icon iconProps={{ className: classes.groupDeleteIcon }} path={deleteIcon} />
              {!buttonText ? (changesRequest ? 'Return to edit' : 'Delete group'): buttonText}
            </div>
          </div>
        </div>
      </div>
    </div>
  </Modal>
);

ConfirmationModal.propTypes = {
  onCancel: PropTypes.func.isRequired,
  onSuccess: PropTypes.func.isRequired,
  message: PropTypes.any.isRequired,
  headerText: PropTypes.string.isRequired,
  buttonCancel: PropTypes.string.isRequired,
  classes: PropTypes.object.isRequired,
  modelProps: PropTypes.object.isRequired,
  changesRequest: PropTypes.bool,
  buttonText: PropTypes.string,
};

ConfirmationModal.defaultProps = {
  changesRequest: false,
  buttonText: '',
};

export default withTheme(injectSheet(style)(ConfirmationModal));
