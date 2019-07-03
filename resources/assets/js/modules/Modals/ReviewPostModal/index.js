/* eslint-disable react/jsx-indent */
/* eslint-disable react/prop-types */
import React, {
  useCallback, useRef, useState, useEffect,
} from 'react';
import injectSheet from 'react-jss';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import remove from 'lodash/remove';
import MediaQuery from 'react-responsive';
import * as Yup from 'yup';
import axios from 'axios';
import uuid from 'uuid';
import { isEqual, isEmpty, get } from 'lodash';
import DatePicker, { CalendarContainer } from 'react-datepicker';
import { useDropzone } from 'react-dropzone';
import { Formik } from 'formik';
import moment from 'moment';
import { globalConstants } from '../../../utils/constants';
import withTheme from '../../../utils/hoc/withTheme';
import {
  Modal, Button, Input, TextArea, Breadcrumb, RoundIcon, Image,
  TagInput, Icon, Heading, PostBottom, Spinner, AttachmentTile, ProgressBar,
} from '../../../components';
import {
  DocumentIcon, userGroup, ImgAttachment, closeIcon, phoneIcon, supportIcon, infoIcon, videoImg,
} from '../../../images';
import {
  getShareMembers, addReviewApi, getS3Token, deleteAttachment, editReviewApi,
} from '../../../api/app';
import { fileUpload } from '../../../api/s3';
import { styles } from '../styles';
import './review_post_modal.scss';
import { seperateFiles, convertAttachmentData } from '../../../utils/methods';

const { userImg } = globalConstants;
const initialMemberData = {
  value: '',
  showMembers: [],
};
const initialFormValues = {
  title: '', review_date: '', description: '', attachments: {}, conducted_via: null,
};
const ReviewPostModal = React.memo(({
  modelProps, classes, disabled,
  group, onGroupClick, onSuccess, formValues, editReviewPost,
}) => {
  const postFormRef = useRef(null);
  const pickerRef = useRef(null);
  const [date, setDate] = useState({
    selected: get(formValues, 'review_date', null) ? new Date(formValues.review_date) : '',
    value: new Date(),
  });

  const [members, setMembers] = useState([]);
  const [memberList, setMemberList] = useState(initialMemberData);
  const [addedMembers, addMembers] = useState(get(formValues, 'attendees', null) ? formValues.attendees : []);
  const [membersShow, setMembersShow] = useState(false);
  const [loader, setLoader] = useState(false);
  const [postReviewFormValues, setPostFormValues] = useState(formValues || initialFormValues);
  const [fileError, setFileError] = useState('');
  const [attachments, setAttachments] = useState(get(formValues, 'attachments') || {});
  const [deletedAttachments, setDeleteAttachments] = useState([]);
  const {
    loaders, images, files, videos,
  } = seperateFiles(attachments);

  const getMembers = (keyword) => {
    getShareMembers(keyword).then(({ data: { users } }) => {
      setMembers(users);
    });
  };

  const changeValue = (event) => {
    const { name, value } = event.target;
    if (name === 'member' && value.startsWith('@')) {
      const keyword = value.split('@');
      const users = members.filter(
        el => el.full_name.toLowerCase().includes(keyword[1].toLowerCase()),
      );
      setMemberList(previous => ({
        ...previous,
        showMembers: users,
        value,
      }));
    } else {
      setMemberList(initialMemberData);
    }
  };

  useEffect(
    () => {
      getMembers('');
    },
    [],
  );

  const removeMember = (member) => {
    remove(addedMembers, member);
    addMembers([
      ...addedMembers,
    ]);
  };

  const selectMember = (member) => {
    addMembers(previousMembers => [
      ...previousMembers,
      member,
    ]);
    setMemberList(initialMemberData);
  };

  const saveFormValues = () => {
    const { state: { values } } = postFormRef.current;
    setPostFormValues(values);
  };

  const detectFormChanges = () => {
    const { state: { values } } = postFormRef.current;
    if (!isEqual(values, formValues || initialFormValues) || (!isEmpty(attachments) && !formValues) || loaders.length) {
      return true;
    }
    if (formValues) {
      if (formValues && deletedAttachments.length) {
        return true;
      }
      if (!isEqual(formValues.attendees, addedMembers)) {
        return true;
      }
    }

    return false;
  };

  const checkDiscard = () => {
    saveFormValues();
    const isChange = detectFormChanges();
    modelProps.onHide(isChange);
  };

  const updateGroup = () => {
    saveFormValues();
    const isChange = detectFormChanges();
    onGroupClick(isChange);
  };

  const getToken = async () => getS3Token().then(({ data }) => data);

  const onDrop = useCallback((acceptedFiles) => {
    if (acceptedFiles.length) {
      setFileError('');
      getToken().then((updatedToken) => {
        acceptedFiles.forEach((file) => {
          const attachmentKey = uuid.v4();
          const { CancelToken } = axios;
          const source = CancelToken.source();
          fileUpload(file, updatedToken, ({ progress }) => {
            setAttachments(previousattachments => ({
              ...previousattachments,
              [attachmentKey]: {
                type: 'loaders',
                progress,
                source,
                id: attachmentKey,
              },
            }));
          }, source).catch(() => {
            setAttachments((previousattachments) => {
              const checkAttachments = previousattachments;
              if (checkAttachments[attachmentKey]) {
                delete checkAttachments[attachmentKey];
              }
              return ({
                ...checkAttachments,
              });
            });
          }).then((res) => {
            if (res) {
              setAttachments(previousattachments => ({
                ...previousattachments,
                [attachmentKey]: res,
              }));
            }
          });
        });
      });
    } else {
      setFileError('Ups. Wrong extension type. Please upload .pdf,.docx,.ppt,.pptx,.mp4,.doc,.xls,.xlsx,.csv,.mov,.MOV,.png,.jpeg,.jpg files.');
    }
  });

  const { getRootProps, getInputProps } = useDropzone({
    onDrop,
    accept: '.pdf,.docx,.ppt,.pptx,.mp4,.doc,.xls,.xlsx,.csv,.mov,.MOV,.png,.jpeg,.jpg',
  });
  const cancelRequest = ({ source }) => {
    source.cancel();
  };

  const deleteAttachmentApi = (attach) => {
    delete attachments[attach.id];
    setAttachments({
      ...attachments,
    });
    if (!get(attach, 'exact')) {
      const url = attach.PostResponse.Location['#text'];
      deleteAttachment({ url }).catch(() => {});
    } else {
      deletedAttachments.push(attach);
      return setDeleteAttachments([
        ...deletedAttachments,
      ]);
    }
    const url = attach.PostResponse.Location['#text'];
    return deleteAttachment({ url }).catch(() => {});
  };


  const fromTopClick = (index) => {
    switch (index) {
      case 0:
        return onGroupClick();
      default:
        return false;
    }
  };

  function MyCalendar({
    className, children, setFieldValue, setFieldTouched,
  }) {
    return (
      <CalendarContainer className={className}>
        {children}
        <div className="calendar-footer text-right">
          <button type="button" className="btn transparent-btn" onClick={() => pickerRef.current.setOpen(false)}>Cancel</button>
          <button
            type="button"
            className="btn btn-primary"
            onClick={() => {
              setDate(previous => ({
                ...previous,
                selected: previous.value,
              }));
              setFieldTouched('review_date', true);
              setFieldValue('review_date', 'change');
              pickerRef.current.setOpen(false);
            }}
          >
            Apply
          </button>
        </div>
      </CalendarContainer>
    );
  }
  const { value, showMembers } = memberList;
  const attendeeResult = addedMembers.map((member => member.id));
  return (
    <Modal
      modelProps={{ ...modelProps, dialogClassName: classes.addPostPopup, onHide: checkDiscard }}
      headerText={(
        <>
          <MediaQuery query="(min-device-width: 767px)">
            <Breadcrumb items={['Group', 'Review']} active={1} onClick={fromTopClick} />
          </MediaQuery>
          <MediaQuery query="(max-device-width: 767px)">
            <div className="post-submit-column">
              {!formValues ? 'Log a review' : 'Edit a review'}
            </div>
          </MediaQuery>
        </>
    )}
    >
      <div className="review-container">
        <div className="review-form-head">
          <div className="review-input">
            <Formik
              ref={postFormRef}
              initialValues={postReviewFormValues}
              onSubmit={(payload, { setSubmitting }) => {
                setLoader(true);
                const updatedAttachments = convertAttachmentData(attachments);
                const updatedValues = {
                  ...payload,
                  attachments: updatedAttachments,
                  review_date: moment(date.selected).format('YYYY-MM-DD'),
                  group_id: group.id,
                  space_user_ids: attendeeResult,
                  description: payload.description.replace(/\r?\n/g, '<br />'),
                };
                const postReviewData = {
                  ...updatedValues,
                };

                if (editReviewPost) {
                  delete postReviewData.attendees;
                  const editReviewData = {
                    ...postReviewData,
                    delete_attachments: deletedAttachments,
                  };
                  editReviewApi(editReviewData, editReviewPost).then((res) => {
                    setSubmitting(false);
                    setLoader(false);
                    onSuccess(res);
                  }).catch(() => {
                    setLoader(false);
                    setSubmitting(false);
                  });
                } else {
                  addReviewApi(postReviewData).then((res) => {
                    setSubmitting(false);
                    setLoader(false);
                    onSuccess(res);
                  }).catch(() => {
                    setLoader(false);
                    setSubmitting(false);
                  });
                }
              }}
              validationSchema={Yup.object().shape({
                title: Yup.string().trim()
                  .required('Please enter a title'),
                description: Yup.string().trim()
                  .required('Please add some information about the review')
                  .min(5, 'Enter 5 or more characters'),
                review_date: Yup.string()
                  .required('Please add Review Date'),
                conducted_via: Yup.number().nullable()
                  .required('Please select how the review was conducted'),
              })}
            >
              {(props) => {
                const {
                  handleChange,
                  handleSubmit,
                  isSubmitting,
                  values,
                  touched,
                  errors,
                  setFieldValue,
                  setFieldTouched,
                } = props;
                return (
                  <>
                  <div className="review-form-col">
                    <form method="post" onSubmit={handleSubmit}>
                      <MediaQuery query="(max-device-width: 767px)">
                        <div className="post-heading-mobile">
                          <Heading>What do you want to talk about copy for review?</Heading>
                        </div>
                      </MediaQuery>
                      <div className={classes.topPanel}>
                        <MediaQuery query="(min-device-width: 767px)">
                          <Image img={userImg} size="img66" />
                        </MediaQuery>
                        <div className={classnames(classes.inputContainer, 'mobile-review-post-column')}>
                          {!disabled && (
                          <Input
                            inputProps={{
                              disabled,
                              value: values.title,
                              placeholder: 'Review Title',
                              name: 'title',
                              onChange: handleChange,
                              maxLength: 30,
                              className: classnames(classes.postInput, classes.subjectInput),
                            }}
                            error={(touched.title && errors.title) ? errors.title : ''}
                          />
                          )}
                          <div className="date-picker-col">
                            <DatePicker
                              selected={date.selected}
                              value={date.selected}
                              onChange={(d) => {
                                if (typeof d === 'object') {
                                  setDate(previous => ({
                                    ...previous,
                                    value: d,
                                  }));
                                }
                              }}
                              placeholderText="Date of Review"
                              calendarContainer={calendarProps => MyCalendar({ ...calendarProps, setFieldValue, setFieldTouched })}
                              className={classnames(classes.dateInput)}
                              maxDate={moment(new Date()).endOf('month').toDate()}
                              shouldCloseOnSelect={false}
                              ref={pickerRef}
                            />
                            <span className="review-date-error">{(touched.review_date && errors.review_date) && errors.review_date}</span>
                          </div>
                          <MediaQuery query="(min-device-width: 767px)">
                            <TextArea
                              inputProps={{
                                value: values.description,
                                minLength: 5,
                                placeholder: disabled ? 'Click here to add text, files or links...' : 'About the Review...',
                                name: 'description',
                                onChange: (event) => {
                                  handleChange(event);
                                },
                                className: classnames(classes.postDescription),
                              }}
                              errorClass="review-error"
                              error={(touched.description && errors.description) ? errors.description : ''}
                            />
                          </MediaQuery>
                          <MediaQuery query="(max-device-width: 767px)">
                            <TextArea
                              inputProps={{
                                value: values.description,
                                minLength: 5,
                                placeholder: disabled ? 'Click here to add text, files or links...' : 'About the review... can we recreate it as question?',
                                name: 'description',
                                onChange: (event) => {
                                  handleChange(event);
                                },
                                className: classnames(classes.postDescription),
                              }}
                              errorClass="review-error"
                              error={(touched.description && errors.description) ? errors.description : ''}
                            />
                          </MediaQuery>
                        </div>
                        <MediaQuery query="(min-device-width: 767px)">
                          <Button buttonProps={{ type: 'submit', disabled: isSubmitting || loaders.length }}>Post</Button>
                        </MediaQuery>
                      </div>
                    </form>
                    <div className="add-media-col d-flex">
                      <div {...getRootProps({ className: 'dropzone' })}>
                        <input {...getInputProps()} />
                        <Button icon={DocumentIcon} buttonProps={{ variant: 'light', className: classes.button }} rounded> Add review document(s) </Button>
                      </div>
                      <div {...getRootProps({ className: 'dropzone' })}>
                        <input {...getInputProps()} />
                        <Button buttonProps={{ variant: 'light', className: classes.button }} rounded> Add action log </Button>
                      </div>
                      <div {...getRootProps({ className: 'dropzone' })}>
                        <input {...getInputProps()} />
                        <Button buttonProps={{ variant: 'light', className: classes.button }} rounded> Add media </Button>
                      </div>
                    </div>
                    <div className="error-msg">{fileError}</div>
                  </div>
              {Object.keys(attachments).length ? (
              <div className="attachment-process-col">
                <div>
                  <ProgressBar loaders={loaders} cancelRequest={cancelRequest} />
                </div>
                <div className={classes.imagesContainer}>
                  {
                    images.map(image => (
                      <div key={image.id} className={classes.singleImage}>
                        <img src={get(image, 'exact') || URL.createObjectURL(image.file)} size="img131" alt="img" className={classes.attImage} />
                        <div className={classes.imageDeleteIcon}>
                          <RoundIcon
                            icon={closeIcon}
                            iconProps={{ className: classes.cancelIcon }}
                            onClick={() => deleteAttachmentApi(image)}
                          />
                        </div>
                      </div>
                    ))
                  }
                </div>
                <AttachmentTile files={files} onDeleteAttachment={deleteAttachmentApi} isDelete />
                <AttachmentTile
                  files={videos}
                  onDeleteAttachment={deleteAttachmentApi}
                  isDelete
                  icon={videoImg}
                />
              </div>
              ) : null}
                <div className="review-conducted d-flex">
                  <div className="review-conducted-info">
                    <MediaQuery query="(min-device-width: 767px)">
                      <Heading>Review conducted via</Heading>
                    </MediaQuery>
                    <MediaQuery query="(max-device-width: 767px)">
                      <Heading>Review conducted via:</Heading>
                    </MediaQuery>
                  </div>
                  <div className="conducted-via d-flex flex-column">
                    <div className="conducted-via-btn">
                      <Button
                        icon={userGroup}
                        buttonProps={{ variant: 'light', className: classes.button, onClick: () => setFieldValue('conducted_via', 0) }}
                        rounded
                        active={values.conducted_via === 0}
                      >
                        F2F
                      </Button>
                      <Button
                        icon={phoneIcon}
                        buttonProps={{ variant: 'light', className: classes.button, onClick: () => setFieldValue('conducted_via', 1) }}
                        rounded
                        active={values.conducted_via === 1}
                      >
                        Call
                      </Button>
                      <Button
                        icon={supportIcon}
                        buttonProps={{ variant: 'light', className: classes.button, onClick: () => setFieldValue('conducted_via', 2) }}
                        rounded
                        active={values.conducted_via === 2}
                      >
                        Video conference
                      </Button>
                      <Button
                        icon={ImgAttachment}
                        buttonProps={{ variant: 'light', className: classes.button, onClick: () => setFieldValue('conducted_via', 3) }}
                        rounded
                        active={values.conducted_via === 3}
                      >
                        Shared document
                      </Button>
                    </div>
                    <div className="conduct-error">{(touched.conducted_via && errors.conducted_via) ? errors.conducted_via : ''}</div>
                  </div>
                </div>
                  </>
                );
              }}
            </Formik>
          </div>
        </div>

        <div className="add-attendees d-flex flex-column">
          <MediaQuery query="(min-device-width: 767px)">
            <Heading>Add attendees</Heading>
          </MediaQuery>
          <MediaQuery query="(max-device-width: 767px)">
            <Heading>Add attendees:</Heading>
          </MediaQuery>
          <div className="alert-message d-flex align-items-start">
            <Icon path={infoIcon} />
            <Heading>
              You can only add attendees who are part of this Share.
              Adding attendees does not give them permission to access this review.
            </Heading>
          </div>
          <TagInput
            inputProps={{ onChange: changeValue, name: 'member', value }}
            users={showMembers}
            selectedMembers={addedMembers}
            onSelect={selectMember}
            onRemove={(member) => {
              removeMember(member);
            }}
            showApiError={null}
            showMemberList={memberList}
          />
        </div>
        <MediaQuery query="(max-width: 767px)">
          <div className="post-submit-btn">
            <button
              className="btn btn-post"
              type="button"
              disabled={loaders.length}
              onClick={() => {
                if (postFormRef.current) {
                  postFormRef.current.handleSubmit();
                }
              }}
            >
              {editReviewPost ? 'Save' : 'Log a review'}
            </button>
          </div>
        </MediaQuery>
        <div className="review-bottom">
          <PostBottom
            group={group}
            seePost="Who will see this review?"
            seeText="See"
            memberList={membersShow}
            showMemberList={setMembersShow}
            onGroupClick={updateGroup}
          />
        </div>
        {loader && <Spinner />}
      </div>
    </Modal>
  );
});

ReviewPostModal.propTypes = {
  modelProps: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  disabled: PropTypes.bool,
  handleChange: PropTypes.func,
  handleSubmit: PropTypes.func,
  isSubmitting: PropTypes.bool,
  onGroupClick: PropTypes.func,
  group: PropTypes.object,
  formProps: PropTypes.object,
  values: PropTypes.object,
  touched: PropTypes.any,
  errors: PropTypes.object,
  formValues: PropTypes.object,
};

ReviewPostModal.defaultProps = {
  disabled: false,
  handleChange: () => {},
  handleSubmit: () => {},
  isSubmitting: false,
  onGroupClick: () => {},
  formProps: {},
  group: {},
  values: {},
  errors: {},
  formValues: null,
  touched: false,
  editReviewPost: null,
};

export default withTheme(injectSheet(styles)(ReviewPostModal));
