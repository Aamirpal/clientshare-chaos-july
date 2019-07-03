import React, { useState, useRef, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';
import useDebouncedCallback from 'use-debounce/lib/callback';
import {
  difference, isEqual, isEmpty, get,
} from 'lodash';
import * as Yup from 'yup';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import urlRegex from 'url-regex';
import PropTypes from 'prop-types';
import axios from 'axios';
import uuid from 'uuid';
import MediaQuery from 'react-responsive';
import { Formik } from 'formik';
import PostBottom from './PostBottom';
import withTheme from '../../../utils/hoc/withTheme';
import { globalConstants } from '../../../utils/constants';
import {
  ImgGalary, ImgVideo, ImgAttachment, closeIcon, videoImg,
} from '../../../images';
import {
  // eslint-disable-next-line max-len
  Image, Button, Input, Heading, RoundIcon, TextArea, Modal, Breadcrumb, ContentLoader, Spinner, AttachmentTile, UrlPreview, ProgressBar,
} from '../../index';
import { styles } from './styles';
import {
  addPostApi, getS3Token, deleteAttachment, getUrlData, editPostApi,
} from '../../../api/app';
import { fileUpload } from '../../../api/s3';
import { seperateFiles, convertAttachmentData } from '../../../utils/methods';

const { userImg } = globalConstants;
const initialEmbed = {
  embedData: null,
  loading: false,
};
const initialFormValues = {
  post_subject: '', post_description: '', attachments: {},
};

const AddPostForm = React.memo(({
  // eslint-disable-next-line max-len
  classes, disabled, onSuccess, modelProps, category, group, onGroupClick, onCategoryClick, formValues, formEmbed, editPost, post,
}) => {
  const postFormRef = useRef(null);
  const [postFormValues, setPostFormValues] = useState(formValues || initialFormValues);
  const [memberList, showMemberList] = useState(false);
  const [attachments, setAttachments] = useState(get(formValues, 'attachments') || {});
  const [fileError, setFileError] = useState('');
  const [embed, setEmbed] = useState(formEmbed || initialEmbed);
  const [disabledEmbed, setDisabledEmbed] = useState([]);
  const [loader, setLoader] = useState(false);
  const [deletedAttachments, setDeleteAttachments] = useState([]);

  const getEmbedApi = (values) => {
    const avaliableUrls = difference(values, disabledEmbed);
    if (avaliableUrls.length) {
      setEmbed({ ...embed, loading: true });
      getUrlData(avaliableUrls[0]).then((data) => {
        setEmbed({ ...embed, embedData: data, loading: false });
      });
    }
  };

  const [debouncedCallback] = useDebouncedCallback(
    (values) => {
      if (!embed.embedData) {
        getEmbedApi(values);
      } else if (embed.embedData.url_list.split(',')[0] !== values[0]) {
        getEmbedApi(values);
      }
    },
    1000,
  );

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
      setDeleteAttachments([
        ...deletedAttachments,
      ]);
    }
  };

  const checkUrl = (value) => {
    if (value) {
      const urls = value.match(urlRegex());
      if (urls && urls.length > 0) {
        debouncedCallback(urls);
      } else {
        setEmbed({
          ...initialEmbed,
        });
      }
    } else {
      setEmbed({
        ...initialEmbed,
      });
    }
  };

  const removeEmbed = (embedD) => {
    setEmbed({
      ...initialEmbed,
    });
    disabledEmbed.push(embedD.url_list.split(',')[0]);
    setDisabledEmbed(disabledEmbed);
  };

  const saveFormValues = () => {
    const { state: { values } } = postFormRef.current;
    setPostFormValues(values);
  };

  const detectFormChanges = () => {
    const { state: { values } } = postFormRef.current;
    if (!isEqual(values, formValues || initialFormValues) || (!isEmpty(attachments) && !formValues)) {
      return true;
    }

    if (formValues && deletedAttachments.length) {
      return true;
    }
    if (formValues && (!isEqual(embed.embedData, formEmbed.embedData) || !isEqual(attachments, formValues.attachments))) {
      return true;
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

  const updateCategory = () => {
    saveFormValues();
    const isChange = detectFormChanges();
    onCategoryClick(isChange);
  };

  const fromTopClick = (index) => {
    switch (index) {
      case 0:
        return updateCategory();
      case 1:
        return updateGroup();
      default:
        return false;
    }
  };

  const {
    loaders, images, files, videos,
  } = seperateFiles(attachments);
  const { embedData, loading } = embed;
  return (
    <Modal
      modelProps={{ ...modelProps, dialogClassName: classes.addPostPopup, onHide: checkDiscard }}
      headerText={(
        <>
          <MediaQuery query="(min-device-width: 767px)">
            {!formValues ? (
              <Breadcrumb items={['Category', 'Group', 'Post']} active={2} onClick={fromTopClick} />
            ) : 'Edit a Post'}
          </MediaQuery>
          <MediaQuery query="(max-device-width: 767px)">
            <div className="post-submit-column">
              {!formValues ? 'Create a post' : 'Edit a post'}
            </div>
          </MediaQuery>
        </>
)}
    >
      <div className="mobile-post-add">
        <div className={classnames(classes.container, 'post-container')}>
          <Formik
            ref={postFormRef}
            initialValues={postFormValues}
            onSubmit={(payload, { setSubmitting }) => {
              setLoader(true);
              const updatedAttachments = convertAttachmentData(attachments);
              const updatedValues = {
                ...payload,
                attachments: updatedAttachments,
                url_preview: embedData,
              };
              const postApiData = {
                ...updatedValues,
                space_category_id: category.category_id,
                group_id: group.id,
                post_description: payload.post_description.replace(/\r?\n/g, '<br />'),
              };

              if (editPost) {
                if (detectFormChanges() || (get(post, 'group_id')) !== group.id || (get(post, 'space_category_id')) !== category.category_id) { // Check if user made changes
                  const editPostData = {
                    ...postApiData,
                    delete_attachments: deletedAttachments,
                  };
                  return editPostApi(editPost, editPostData).then((res) => {
                    setSubmitting(false);
                    setLoader(false);
                    onSuccess(res);
                  }).catch(() => {
                    setLoader(false);
                    setSubmitting(false);
                  });
                }
                return modelProps.onHide(false);
              }

              return addPostApi(postApiData).then((res) => {
                setSubmitting(false);
                setLoader(false);
                onSuccess(res);
              }).catch(() => {
                setLoader(false);
                setSubmitting(false);
              });
            }}
            validationSchema={Yup.object().shape({
              post_subject: Yup.string().trim()
                .required('Please add a post subject'),
              post_description: Yup.string().trim()
                .required('Please add a post description'),
            })}
          >
            {(props) => {
              const {
                values,
                errors,
                handleChange,
                handleSubmit,
                isSubmitting,
                touched,
              } = props;
              return (
                <form method="post" onSubmit={handleSubmit}>
                  <MediaQuery query="(max-device-width: 767px)">
                    <div className="post-heading-mobile">
                      <Heading>What do you want to talk about?</Heading>
                    </div>
                  </MediaQuery>
                  <div className={classnames(classes.topPanel, 'add-post-input-group')}>
                    <MediaQuery query="(min-device-width: 767px)">
                      <Image img={userImg} size="img66" />
                    </MediaQuery>
                    <div className={classes.inputContainer}>
                      {!disabled && (
                      <Input
                        inputProps={{
                          disabled,
                          value: values.post_subject,
                          placeholder: 'Subject',
                          name: 'post_subject',
                          onChange: handleChange,
                          className: classnames(classes.postInput, classes.subjectInput),
                        }}
                        error={(touched.post_subject && errors.post_subject) ? errors.post_subject : ''}
                      />
                      )}
                      <TextArea
                        inputProps={{
                          disabled,
                          value: values.post_description,
                          placeholder: disabled ? 'Click here to add text, files or links...' : 'What do you want to talk about?',
                          name: 'post_description',
                          onChange: (event) => {
                            checkUrl(event.target.value);
                            handleChange(event);
                          },
                          className: classnames(classes.postDescription),
                        }}
                        error={(touched.post_description && errors.post_description) ? errors.post_description : ''}
                      />
                    </div>

                    <MediaQuery query="(min-device-width: 767px)">
                      <Button buttonProps={{ type: 'submit', disabled: isSubmitting || loaders.length }}>
                        {editPost ? 'Save' : 'Post'}
                      </Button>
                    </MediaQuery>
                  </div>
                  {embedData ? (
                    <UrlPreview embedData={embedData} removeEmbed={removeEmbed} />
                  ) : null}
                  {loading && <ContentLoader className={classes.embedContainer} height={120} />}
                </form>
              );
            }}
          </Formik>

          <div className={classes.bottomPanel}>
            <div {...getRootProps({ className: 'dropzone' })}>
              <input {...getInputProps()} disabled={disabled} />
              <Button icon={ImgGalary} buttonProps={{ variant: 'light', className: classes.button }} rounded> Images </Button>
            </div>
            <div {...getRootProps({ className: 'dropzone' })}>
              <input {...getInputProps()} disabled={disabled} />
              <Button icon={ImgVideo} buttonProps={{ variant: 'light', className: classes.button }} rounded> Videos </Button>
            </div>
            <div {...getRootProps({ className: 'dropzone' })}>
              <input {...getInputProps()} disabled={disabled} />
              <Button icon={ImgAttachment} buttonProps={{ variant: 'light', className: classes.button }} rounded> Files </Button>
            </div>


          </div>
          <>
            <div className={classes.filesError}>{fileError}</div>
            {Object.keys(attachments).length ? (
              <>
                <div>
                  <Heading as="h4" headingProps={{ className: classes.attachmentTitle }}>Attachment added :</Heading>
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
              </>
            ) : null}

          </>

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
              {editPost ? 'Save' : 'Post'}
            </button>
          </div>
        </MediaQuery>
        <PostBottom
          category={category}
          group={group}
          onGroupClick={updateGroup}
          onCategoryClick={updateCategory}
          memberList={memberList}
          showMemberList={showMemberList}
        />
        {loader && <Spinner />}
      </div>
    </Modal>
  );
});

AddPostForm.propTypes = {
  classes: PropTypes.object.isRequired,
  disabled: PropTypes.bool,
  handleChange: PropTypes.func,
  values: PropTypes.object,
  handleSubmit: PropTypes.func,
  isSubmitting: PropTypes.bool,
  errors: PropTypes.object,
  onSuccess: PropTypes.func.isRequired,
  modelProps: PropTypes.object.isRequired,
  category: PropTypes.object.isRequired,
  group: PropTypes.object.isRequired,
  onGroupClick: PropTypes.func.isRequired,
  onCategoryClick: PropTypes.func.isRequired,
  touched: PropTypes.any,
  formValues: PropTypes.object,
  formEmbed: PropTypes.object,
  editPost: PropTypes.any,
  post: PropTypes.object,
};

AddPostForm.defaultProps = {
  disabled: false,
  handleChange: () => {},
  handleSubmit: () => {},
  isSubmitting: false,
  values: {},
  errors: {},
  touched: false,
  formValues: null,
  formEmbed: null,
  editPost: null,
  post: null,
};


export default withTheme(injectSheet(styles)(AddPostForm));
