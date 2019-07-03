import React, { useState, useEffect, useRef } from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import Spinner from 'react-bootstrap/Spinner';
import { isEmpty, remove, isEqual } from 'lodash';
import { Formik } from 'formik';
import * as Yup from 'yup';
import withTheme from '../../utils/hoc/withTheme';
import {
  getShareMembers,
  groupCreate,
  groupUpdate,
  deleteGroupMembers,
} from '../../api/app';
import GroupSvg from '../../images/group.svg';
import deleteIcon from '../../images/delete_icon.svg';
import styles from './styles';

import {
  Icon, Modal, Button, TagInput,
} from '../../components';


const initFormValues = { group_name: '', member: '', addedMembers: [] };
const changes = { status: false, value: '' };

const CreateGroupModal = React.memo(({
  classes, modelProps, editItem, onSuccess, updateGroup, manageEditDelete, isEditDelete, onDiscard, fetchGroups, editGroupValues, type,
}) => {
  initFormValues.addedMembers = (editItem && editItem.members) ? editItem.members : [];
  initFormValues.group_name = (editItem && editItem.name) ? editItem.name : '';
  changes.value = (editItem && editItem.name) ? editItem.name : '';
  const [values, setValues] = useState(initFormValues);
  const [memberDialog, setMemberDelete] = useState({});
  const [members, setMembers] = useState([]);
  const [apiError, setApiError] = useState('');
  const [memberList, setMemberList] = useState(false);
  const [change, setChange] = useState(changes);
  const inputRef = useRef(null);

  const getMembers = (keyword) => {
    getShareMembers(keyword).then(({ data: { users } }) => {
      setMembers(users);
    });
  };

  const changeValue = (event) => {
    const { name, value } = event.target;
    setValues({
      ...values,
      [name]: value,
    });

    if (name === 'member' && value.startsWith('@')) {
      const keyword = value.split('@');
      const users = members.filter(
        el => el.full_name.toLowerCase().includes(keyword[1].toLowerCase()),
      );
      setMemberList(users);
    } else {
      setMemberList([]);
    }
    if (apiError.user_ids) {
      delete apiError.user_ids;
      setApiError(apiError);
    }
  };

  useEffect(
    () => {
      getMembers('');
    },
    [],
  );

  const selectMember = (member) => {
    const { addedMembers } = values;
    setValues({
      ...values,
      addedMembers: [
        ...addedMembers,
        member,
      ],
      member: '',
    });
    setMemberList([]);
  };

  const inputChanges = (value) => {
    setValues({
      ...values,
      group_name: value,
    });
    if (apiError.name) {
      delete apiError.name;
      setApiError(apiError);
    }
  };

  const removeMember = (member) => {
    const { addedMembers } = values;
    remove(addedMembers, member);
    setValues({
      ...values,
      addedMembers,
      member: '',
    });
  };

  const discardPost = () => {
    const { onHide } = modelProps;
    if (type === 'create') {
      return onHide('create');
    }
    const { state } = inputRef.current;
    if (((state.values.group_name !== editGroupValues.name) || !isEqual(editGroupValues.members, values.addedMembers)) && !isEmpty(editItem)) {
      onDiscard(values.group_name, values.addedMembers);
    } else {
      onHide('edit');
    }
  };

  const removeMemberWithEdit = () => {
    if (memberDialog && memberDialog.group_user_id) {
      deleteGroupMembers(memberDialog.group_user_id).then(() => {
        const { addedMembers } = values;
        remove(addedMembers, memberDialog);
        setValues({
          ...values,
          addedMembers,
          member: '',
        });
        setMemberDelete(false);
        fetchGroups();
      }).catch((error) => {
        setApiError(error.message.Validation_messages);
      });
    }
  };

  const normalizeData = (data) => {
    const result = [];
    if (data) {
      data.forEach((item, index) => {
        result[index] = (item.user_id);
      });
      return result;
    }
    return result;
  };

  let groupName = '';
  let titleText = 'Create a group';
  let groupId = 0;
  if (type === 'edit') {
    titleText = 'Edit group';
  }
  if (editItem && editItem.name) {
    groupName = editItem.name;

    groupId = editItem.group_id;
  }
  if (isEditDelete) {
    const visible = true;
    return (
      <Modal modelProps={{ className: 'sm-popup delete-group-modal' }} visible={visible} headerText="Delete group" onClose={() => { manageEditDelete(groupId, values.group_name, values.addedMembers); }}>
        <div className={classes.modalContainer}>
          <div className="edit-delete-container">
            <div className="edit-group-popup">
              <p className={classes.modalBodyHeading}>Do you want to permanently delete this group?</p>
              <div className="w-100 d-flex justify-content-end">
                <div onClick={() => { manageEditDelete(groupId, values.group_name, values.addedMembers); }} className="transparent-btn">Cancel</div>
                <div onClick={() => { manageEditDelete(groupId, values.group_name, values.addedMembers); }} className="red-btn">
                  <Icon className="dwwdd" path={deleteIcon} />
                  Delete group
                </div>
              </div>
            </div>
          </div>
        </div>
      </Modal>
    );
  }

  if (memberDialog && memberDialog.group_user_id) {
    const visible = true;
    return (
      <Modal modelProps={{ className: 'sm-popup delete-group-modal' }} visible={visible} headerText="Remove a member" onClose={() => { setMemberDelete(false); }}>
        <div className={classes.modalContainer}>
          <div className="edit-delete-container">
            <div className="edit-group-popup">
              <p className={classes.modalBodyHeading}>Do you want to delete this member?</p>
              <div className="w-100 d-flex justify-content-end">
                <div onClick={() => { setMemberDelete(false); }} className="transparent-btn">Cancel</div>
                <div onClick={() => { removeMemberWithEdit(); }} className="red-btn">
                  <Icon className="dwwdd" path={deleteIcon} />
                  Delete
                </div>
              </div>
            </div>
          </div>
        </div>
      </Modal>
    );
  }

  const updatedModelProps = {
    ...modelProps,
    onHide: discardPost,
  };
  const editForm = () => (
    <Formik
      ref={inputRef}
      initialValues={{ group_name: values.group_name, addedMembers: values.addedMembers }}
      onSubmit={(payload, { setSubmitting }) => {
        if (!isEmpty(editItem)) {
          const formData = Object.assign({}, {
            name: payload.group_name,
            group_id: editItem.group_id,
            user_ids: normalizeData(values.addedMembers),
          });
          groupUpdate(formData).then(() => {
            onSuccess();
          }).catch((error) => {
            const errMsg = (error && error.message && error.message.Validation_messages)
              ? error.message.Validation_messages : 'Oops something went wrong.';
            setApiError(errMsg);
          });
        } else {
          const formData = Object.assign({}, { name: payload.group_name, user_ids: normalizeData(values.addedMembers) });
          groupCreate(formData).then(() => {
            onSuccess();
          }).catch((error) => {
            setApiError(error.message.Validation_messages);
          });
        }
        setSubmitting(false);
      }}
      validationSchema={Yup.object().shape({
        group_name: Yup.string()
          .required('Please add a group name before saving your group'),
      })}
    >
      {(props) => {
        const {
          values,
          touched,
          errors,
          handleChange,
          handleBlur,
          handleSubmit,
        } = props;
        return (
          <form onSubmit={handleSubmit} className={classes.groupFormContainer}>
            { !isEmpty(editItem)
              && (
                <>
                  <div className={classes.groupInputContainer}>
                    <input
                      name="group_name"
                      type="text"
                      placeholder="Type a group name"
                      className={classes.groupInput}
                      value={values.group_name}
                      spellCheck="false"
                      onChange={(event) => {
                        handleChange(event);
                        inputChanges(event.target.value);
                      }}
                      onBlur={handleBlur}
                      autoComplete="off"
                      maxLength="24"
                    />
                    {errors.group_name
    && touched.group_name && <div className={classes.errorMessage}>{errors.group_name}</div>}
                    {apiError.name && <div className={classes.errorMessage}>{apiError.name[0]}</div>}
                  </div>
                  <div className="red-btn" onClick={() => { manageEditDelete(groupId, values.group_name, values.addedMembers); }}>
                    <Icon className="delete-icon" path={deleteIcon} />
            Delete group
                  </div>
                  <div className={classes.buttonContainer}>
                    <Button buttonProps={{ type: 'submit' }}>Save</Button>
                  </div>
                </>

              )}

          </form>
        );
      }}
    </Formik>
  );

  const createForm = () => (
    <Formik
      initialValues={{ group_name: values.group_name }}
      onSubmit={(payload, { setSubmitting }) => {
        if (!isEmpty(editItem)) {
          const formData = Object.assign({}, {
            name: payload.group_name,
            group_id: editItem.group_id,
            user_ids: normalizeData(values.addedMembers),
          });
          groupUpdate(formData).then(() => {
            updateGroup();
          }).catch((error) => {
            const errMsg = (error && error.message && error.message.Validation_messages)
              ? error.message.Validation_messages : 'Oops something went wrong.';
            setApiError(errMsg);
          });
        } else {
          const formData = Object.assign({}, { name: payload.group_name, user_ids: normalizeData(values.addedMembers) });
          groupCreate(formData).then(() => {
            onSuccess();
          }).catch((error) => {
            setApiError(error.message.Validation_messages);
          });
        }
        setSubmitting(false);
      }}
      validationSchema={Yup.object().shape({
        group_name: Yup.string()
          .required('Please add a group name before saving your group'),
      })}
    >
      {(props) => {
        const {
          values,
          touched,
          errors,
          handleChange,
          handleBlur,
          handleSubmit,
        } = props;
        return (
          <form onSubmit={handleSubmit} className={classes.groupFormContainer}>
            <div className={classes.groupInputContainer}>
              <input
                name="group_name"
                type="text"
                placeholder="Type a group name"
                className={classes.groupInput}
                spellCheck="false"
                value={values.group_name}
                onChange={(event) => {
                  handleChange(event);
                  inputChanges(event.target.value);
                }}
                onBlur={handleBlur}
                autoComplete="off"
                maxLength="24"
              />
              {errors.group_name
  && touched.group_name && <div className={classes.errorMessage}>{errors.group_name}</div>}
              {apiError.name && <div className={classes.errorMessage}>{apiError.name[0]}</div>}
            </div>
            { !isEmpty(editItem) && (
            <div className="red-btn" onClick={() => { manageEditDelete(groupId, true); }}>
              <Icon className="delete-icon" path={deleteIcon} />
          Delete group
            </div>
            )}
            <div className={classes.buttonContainer}>
              <Button buttonProps={{ type: 'submit' }}>{ !isEmpty(editItem) ? 'Save' : 'Create'}</Button>
            </div>
          </form>
        );
      }}
    </Formik>
  );

  return (
    <Modal headerText={titleText} modelProps={updatedModelProps}>
      <div className={classes.modalContainer}>
        <div className={classes.topContainer}>
          <span className={classes.groupIconContainer}>
            <Icon path={GroupSvg} />
          </span>
          {!isEmpty(editItem) && type === 'edit' && (
            editForm()
          ) }

          {type === 'create' && (
            createForm()
          )}

          {type === 'edit' && isEmpty(editItem) && (
            <div className={classes.loader}><Spinner animation="border" variant="success" /></div>
          )}

        </div>
        {modelProps.show && (
        <TagInput
          inputProps={{ onChange: changeValue, name: 'member', value: values.member }}
          users={memberList}
          selectedMembers={values.addedMembers}
          onSelect={selectMember}
          onRemove={(member) => {
            if (member.group_user_id) {
              setMemberDelete(member);
            } else {
              removeMember(member);
            }
          }}
          showApiError={apiError}
          showMemberList={memberList}
        />
        ) }
      </div>
    </Modal>
  );
});

CreateGroupModal.propTypes = {
  classes: PropTypes.object.isRequired,
  modelProps: PropTypes.object.isRequired,
  onSuccess: PropTypes.func.isRequired,
  editItem: PropTypes.object,
  updateGroup: PropTypes.func.isRequired,
  manageEditDelete: PropTypes.func.isRequired,
  isEditDelete: PropTypes.bool.isRequired,
  fetchGroups: PropTypes.func,
};

CreateGroupModal.defaultProps = {
  editItem: {},
  changedValue: '',
  fetchGroups: () => {},
  type: 'create',
};

export default withTheme(injectSheet(styles)(CreateGroupModal));
