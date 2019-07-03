import React from 'react';
import PropTypes from 'prop-types';
import BaseModal from 'react-bootstrap/Modal';
import MediaQuery from 'react-responsive';

const Modal = ({
  visible, onClose, headerText, children, modelProps, customHeader, mobileClose,
}) => (
  <BaseModal show={visible} onHide={onClose} centered {...modelProps}>
    {headerText && (
    <BaseModal.Header closeButton>
      {customHeader ? headerText : <BaseModal.Title>{headerText}</BaseModal.Title>}

    </BaseModal.Header>
    )}
    {children}
    {mobileClose && (
      <MediaQuery query="(max-device-width: 767px)">
        <div className="single-post-close" onClick={modelProps.onHide} />
      </MediaQuery>
    )}
  </BaseModal>
);

Modal.propTypes = {
  visible: PropTypes.bool,
  onClose: PropTypes.func,
  headerText: PropTypes.node,
  children: PropTypes.node,
  modelProps: PropTypes.object,
  customHeader: PropTypes.bool,
  mobileClose: PropTypes.bool,
};

Modal.defaultProps = {
  visible: false,
  headerText: '',
  children: '',
  onClose: () => {},
  modelProps: {},
  customHeader: false,
  mobileClose: false,
};

export default Modal;
