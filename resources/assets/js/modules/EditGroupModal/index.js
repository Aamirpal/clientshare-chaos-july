import React from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import Spinner from 'react-bootstrap/Spinner';
import _values from 'lodash/values';
import Modal from '../../components/Modal';
import { GroupContext } from '../../utils/contexts';
import withTheme from '../../utils/hoc/withTheme';
import Heading from '../../components/Heading';
import Icon from '../../components/Icon';
import Everyone from './Everyone';
import { groupDelete } from '../../api/app';
import deleteIcon from '../../images/delete_icon.svg';
import closeIcon from '../../images/close-icon-white.svg';
import { style } from './style';
import './edit-group.scss';

class EditGroupModal extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      isConfirmOpen: false,
      delItem: '',
      apiError: '',
    };
  }

 handleGroupDelete = (id) => {
   const { updateGroup } = this.props;
   groupDelete(id).then(() => {
     this.getGroupsRecord();
     updateGroup();
     this.setState({ isConfirmOpen: false });
   }).catch((error) => {
     const err = error.message.error || 'Oops something went wrong..';
     this.setState({ apiError: err });
   });
 }

  confirmModal = () => {
    const { classes, deleteGroup } = this.props;
    const { delItem, isConfirmOpen, apiError } = this.state;
    return (
      <Modal modelProps={{ className: 'sm-popup delete-group-modal' }} visible={isConfirmOpen} headerText="Delete group" onClose={() => { this.setState({ isOpen: true, isConfirmOpen: false }); }}>
        <div className={classes.modalContainer}>
          <div className={classes.topContainer}>
            <div className="edit-group-popup">
              <p className={classes.modalBodyHeading}>Do you want to permanently delete this group?</p>
              { apiError && (
                <div className="error-box d-flex justify-content-between">
                  <p>{apiError}</p>
                  <span className="error-close" onClick={() => { this.setState({ apiError: '' }); }}>
                    <Icon path={closeIcon} />
                  </span>
                </div>
              )}
              <div className="w-100 d-flex justify-content-end">
                <div onClick={() => { this.setState({ isOpen: true, isConfirmOpen: false }); }} className={classnames(classes.transparentButton)}>Cancel</div>
                <div
                  onClick={() => {
                    this.setState(() => ({ isConfirmOpen: false }));
                    deleteGroup(delItem);
                  }}
                  className={classnames(classes.redBtn)}
                >
                  <Icon iconProps={{ className: classes.groupDeleteIcon }} path={deleteIcon} />
                  Delete group
                </div>
              </div>
            </div>
          </div>
        </div>
      </Modal>
    );
  }

  editDialog = () => {
    const {
      classes, handleEditGroup, deleteGroup, modelProps,
    } = this.props;
    return (
      <Modal headerText="Which group would you want to edit?" modelProps={modelProps}>
        <div className={classes.modalContainer}>
          <div className={classes.topContainer}>
            <div className="edit-group-popup">
              <GroupContext.Consumer>
                {groups => (
                  <div className={classnames(classes.groupsContainer, classes.editGroupsContainer)}>
                    {!_values(groups).length && <div className="loader d-flex w-100 justify-content-center"><Spinner animation="border" variant="success" /></div>}
                    {
                    _values(groups).map(item => (
                      item.is_default ? (
                        <Everyone />
                      ) : (
                        <div key={`data-cls_${item.id}-${item.name}`} className={classnames(classes.groupEditMain, 'group-edit-tile')}>
                          <div onClick={() => { handleEditGroup(item); }} className={classnames(classes.groupEditTile, 'group-tile')}>
                            <Heading as="h5" headingProps={{ className: classes.groupHeading }}>{item.name || ''}</Heading>
                            <div className={classnames(classes.memberCount, 'member-count')}>

                              <span className="member-count-link">
                                {item.group_users_count}
                                {' '}
                                  member
                                {`${item.group_users_count > 1 ? 's' : ''}`}
                              </span>
                              <span className="lock-icon" />
                            </div>
                          </div>
                          <span
                            onClick={() => deleteGroup(item.id)}
                            className={classes.deleteIcon}
                          >
                            <Icon width="12" height="15" path={deleteIcon} />
                          </span>
                        </div>
                      )

                    ))
                  }
                  </div>
                )}
              </GroupContext.Consumer>

            </div>
          </div>
        </div>
      </Modal>
    );
  }

  render() {
    return this.editDialog();
  }
}

EditGroupModal.propTypes = {
  classes: PropTypes.object.isRequired,
  handleEditGroup: PropTypes.func.isRequired,
  updateGroup: PropTypes.func.isRequired,
};


export default withTheme(injectSheet(style)(EditGroupModal));
