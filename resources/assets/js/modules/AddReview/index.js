import React, { useContext, useState } from 'react';
import PropTypes from 'prop-types';
import get from 'lodash/get';
import MediaQuery from 'react-responsive';
import Dropdown from 'react-bootstrap/Dropdown';
import _values from 'lodash/values';
import injectStyle from 'react-jss';
import InfiniteScroll from 'react-infinite-scroller';
import { Facebook } from 'react-content-loader';
import { BusinessReviewContext, GroupContext, UsersContext } from '../../utils/contexts/index';
import { globalConstants } from '../../utils/constants';
import {
  Image, Heading, Icon, AttachmentTile, ConfirmationModal,
  Button, DocumentPreview, Modal, DropDownItems,
} from '../../components/index';
import { MemberTile } from '../../components/Tile';
import { conductedViaList, dropdownItemsReview } from '../../utils/helper';
import { EditReviewModal } from '../Modals';
import withData from '../../utils/hoc/withData';
import withTheme from '../../utils/hoc/withTheme';
import {
  getReviewPost, deleteAttendee, deleteBusinessReview, getUserInfo,
} from '../../api/app';
import { styles } from '../../components/PostFeed/styles';
import {
  globeDarkGray, videoImg, lockDarkGray, deleteIcon, moreIcon,
} from '../../images';
import { getWindowHeight } from '../../utils/methods';
import './add_review.scss';

const {
  clientShareId, isAdmin, userId,
} = globalConstants;

const AddReview = React.memo(({ classes }) => {
  const [reviewData, setReviewData] = useState({});
  const userContext = useContext(UsersContext);
  const { users } = userContext;
  const [deleteModal, setDeleteModal] = useState(false);
  const [editReview, seteditReview] = useState(null);
  const [userInfo, setUserInfo] = useState(null);
  const [preview, showPreview] = useState(false);
  const groups = useContext(GroupContext);
  const images = get(Object.values(reviewData)[0], 'images');
  const videos = get(Object.values(reviewData)[0], 'videos');
  const allAttachments = [...get(Object.values(reviewData)[0], 'images', []), ...get(Object.values(reviewData)[0], 'videos', []), ...get(Object.values(reviewData)[0], 'documents', [])];
  const viewReviewPost = useContext(BusinessReviewContext);
  const {
    fetchReviews, hasMoreReviews, updateReviews, reviewLoadData: { reviews },
  } = viewReviewPost;

  const doAction = ({ key }) => {
    switch (key) {
      case 'delete':
        console.log(reviewData);
        return reviewData;
      case 'edit':
        return false;
      default:
        return false;
    }
  };

  const showBusinessReview = (reviewId, reviewIndex) => {
    if (reviewData[reviewId]) {
      return setReviewData({});
    }
    return getReviewPost(reviewId).then(({ data }) => {
      setReviewData({
        [reviewId]: get(data, 'business_review', {}),
        review_index: reviewIndex,
      });
    }).catch(() => false);
  };

  const deleteReviewApi = () => {
    const updateBusinessReview = reviews;
    const reviewIndex = reviewData.review_index;
    deleteBusinessReview(deleteModal).then(() => {
      setDeleteModal(false);
      updateBusinessReview.splice(reviewIndex, 1);
      updateReviews(updateBusinessReview);
    }).catch(() => false);
  };

  const editReviewPost = (reviewId) => {
    seteditReview(reviewData[reviewId]);
  };

  const removeAttendee = (attendeeId, reviewId, index) => {
    const updatedAttandee = reviewData[reviewId].attendees;
    return deleteAttendee(reviewId, attendeeId).then(() => {
      delete updatedAttandee[index];
      setReviewData({
        [reviewId]: {
          ...reviewData[reviewId],
          attendees: updatedAttandee,
        },
      });
      const updateBusinessReview = reviews;
      const reviewIndex = reviewData.review_index;
      updateBusinessReview[reviewIndex].attendee_count = updatedAttandee.length;
      updateReviews(updateBusinessReview);
    }).catch(() => false);
  };

  const updateUserInfo = (id) => {
    getUserInfo(get(reviewData[id], 'user_id', null)).then(({ data }) => {
      setUserInfo(...data);
    }).catch(() => false);
  };
  return (
    <div className="review-container w-100 d-flex flex-column">
      <InfiniteScroll
        pageStart={0}
        loadMore={fetchReviews}
        hasMore={hasMoreReviews}
        loader={<div className="review-post-loader" key={0}><Facebook /></div>}
        threshold={getWindowHeight(1)}
      >
        {reviews.map((review, reviewIndex) => {
          const isEveryone = get(get(groups, `'${get(review, 'group_id')}'`), 'is_default', null);

          return (
            <div key={review.id}>
              <MediaQuery query="(min-width: 767px)">
                <div className="review-post-col w-100 d-flex flex-column">
                  <div className="review-head w-100 d-flex align-items-center">
                    <div className="head-detail d-flex">
                      <Heading as="h4">{review.title}</Heading>
                    </div>
                    <div className="review-head-icon d-flex align-items-center">
                      <Heading>{review.review_date}</Heading>
                      <div className="gray-icon">
                        <Icon
                          path={isEveryone ? globeDarkGray : lockDarkGray}
                        />
                      </div>
                      <div className="call-btn">
                        <button type="button" className={`btn ${conductedViaList[review.conducted_via]}`}>{conductedViaList[review.conducted_via]}</button>
                      </div>
                      <div className="gray-icon">
                        {review.attendee_count}
                      </div>
                    </div>
                    <div className={reviewData[review.id] ? 'review-collapse minimize' : 'review-collapse'} onClick={() => showBusinessReview(review.id, reviewIndex)}>
                      <Heading as="h6">{ reviewData[review.id] ? 'Minimize' : 'Expand' }</Heading>
                    </div>
                  </div>
                  {reviewData[review.id] ? (
                    <div className="review-minimise-view">
                      {(!reviewData[review.id].maximise_view)
                        ? (
                          <div className="restricted-review d-flex align-items-center">
                            <span className="restricted-profile">
                              <Image
                                img={get(reviewData[review.id], 'user.circular_image_url', null)}
                              />
                            </span>
                            Visibility of this review is restricted, please contact
                            <span className="restricted-name">
                              &nbsp;
                              { reviewData[review.id].user.fullname }
                              &nbsp;
                            </span>
                            for support.
                          </div>
                        )
                        : (
                          <>
                            <div className="review-post-detail">
                              <div className="position-relative d-flex">
                                <div onClick={() => updateUserInfo(review.id)}>
                                  <Image img={get(reviewData[review.id], 'user.circular_image_url', null)} />
                                </div>
                                <div className="mid-text-container">
                                  <div className="name-container">
                                    <Heading as="h4">{reviewData[review.id].user.fullname}</Heading>
                                  </div>
                                  <div className="user-profile">
                                    <Heading>{reviewData[review.id].user_company_profile}</Heading>
                                  </div>
                                </div>
                                {review.user_id === userId && (
                                  <div className="review-action-col d-flex align-items-center">
                                    <div onClick={() => setDeleteModal(review.id)} className="review-delete-btn d-flex align-items-center">
                                      <Icon path={deleteIcon} />
                                      <div className="review-delete-col">
                                        <Heading>Delete</Heading>
                                      </div>
                                    </div>
                                    <div onClick={() => editReviewPost(review.id)} className="review-edit-btn">
                                      <Button className="btn btn-primary">Edit</Button>
                                    </div>
                                  </div>
                                )}

                              </div>
                              <div className="bottom-container">
                                <Heading as="h3" class="review-subject">{review.title}</Heading>
                                <div className="review-description" dangerouslySetInnerHTML={{ __html: review.description }} />
                              </div>


                              {(get(Object.values(reviewData)[0], 'images', []).length || get(Object.values(reviewData)[0], 'videos', []).length || get(Object.values(reviewData)[0], 'documents', []).length) ? (
                                <div className="attachment-container">
                                  {(get(Object.values(reviewData)[0], 'images', []).length || get(Object.values(reviewData)[0], 'videos', []).length) ? (
                                    <div className="image-container">
                                      {images && images.length ? (
                                        <div onClick={() => showPreview(true)}>
                                          <Image position="center" img={images[0].post_file_url} round={false} size="auto" extraClass={classes.post_image} loadingClass={classes.post_image_load} />
                                        </div>
                                      ) : null}
                                      {images && (!images.length && videos.length)
                                        ? (
                                          // eslint-disable-next-line jsx-a11y/media-has-caption
                                          <video width="100%" controls>
                                            <source src={videos[0].post_file_url} type={videos[0].metadata.mimeType} />
                                            Your browser does not support the video tag.
                                          </video>
                                        ) : null}
                                    </div>
                                  ) : null}
                                  <AttachmentTile
                                    files={(get(Object.values(reviewData)[0], 'images', []).length || get(Object.values(reviewData)[0], 'videos', []).length) ? allAttachments.slice(1) : allAttachments}
                                    icon={videoImg}
                                    isPreview
                                    showMore
                                  />

                                </div>
                              ) : null}
                            </div>
                            <div className="attendees-container">
                              <Heading as="h6">Attendees:</Heading>
                              <div className="review-chip-col d-flex flex-wrap">
                                {get(reviewData[review.id], 'attendees', []).map(attendee => (
                                  <div className="review-chip-box" key={attendee.id}>
                                    { get(users[attendee.space_user.user_id], 'user.fullname', '')}
                                  </div>
                                ))}
                              </div>
                            </div>
                          </>
                        )
                      }
                    </div>
                  ) : ''}
                </div>
              </MediaQuery>
              <MediaQuery query="(max-width: 767px)">
                <div className="review-post-col w-100 d-flex flex-column" key={review.id}>
                  <div key={review.id} className="review-head w-100 d-flex align-items-center justify-content-between">
                    <div className="review-mobile-left d-flex align-items-center">
                      <div className="call-btn">
                        <button type="button" className={`btn ${conductedViaList[review.conducted_via]}`}>{conductedViaList[review.conducted_via]}</button>
                      </div>
                      <div className="mobile-review-detail-header">
                        <div className="head-detail d-flex">
                          <Heading as="h4">{review.title}</Heading>
                        </div>
                        <Heading>{review.review_date}</Heading>
                        <span className="mbl-attendee">
                          <Heading>
                            {review.attendee_count}
                            &nbsp;Attendees
                          </Heading>
                        </span>
                      </div>
                    </div>
                    <div className="review-mobile-right d-flex align-items-center">
                      <div className="review-head-icon">
                        <div className="gray-icon">
                          <Icon
                            path={isEveryone ? globeDarkGray : lockDarkGray}
                          />
                        </div>
                        <div className="gray-icon attendee-icon">
                          {review.attendee_count}
                        </div>
                      </div>
                      <div className={reviewData[review.id] ? 'review-collapse minimize' : 'review-collapse'} onClick={() => showBusinessReview(review.id, reviewIndex)}>
                        <Heading as="h6">{ reviewData[review.id] ? 'minimise view' : 'expand view' }</Heading>
                      </div>
                    </div>
                  </div>
                  {reviewData[review.id] ? (
                    <div className="review-minimise-view position-relative">
                      {(!reviewData[review.id].maximise_view)
                        ? (
                          <div className="restricted-review d-flex align-items-center">
                            <span className="restricted-profile" onClick={() => updateUserInfo(review.id)}>
                              <Image
                                img={get(reviewData[review.id], 'user.circular_image_url', null)}
                              />
                            </span>
                            Visibility of this review is restricted, please contact
                            <span className="restricted-name">
                              &nbsp;
                              { reviewData[review.id].user.fullname }
                              &nbsp;
                            </span>
                            for support.
                          </div>
                        )
                        : (
                          <>
                            <div className="review-post-detail">
                              <div className="d-flex">
                                <Image img={get(reviewData[review.id], 'user.circular_image_url', null)} />
                                <div className="mid-text-container">
                                  <div className="name-container">
                                    <Heading as="h4">{reviewData[review.id].user.fullname}</Heading>
                                  </div>
                                  <div className="user-profile">
                                    <Heading>{reviewData[review.id].user_company_profile}</Heading>
                                  </div>
                                </div>
                                {review.user_id === userId && (
                                  <div className="mobile-review-expand">
                                    <Dropdown className={classes.parentDropdown}>
                                      <Dropdown.Toggle className={classes.postfeedDropdown} as="div">
                                        <Icon path={moreIcon} />
                                      </Dropdown.Toggle>
                                      <DropDownItems onClick={doAction} items={dropdownItemsReview} />
                                    </Dropdown>
                                    {/* <div className="review-action-col d-flex align-items-center">
                                      <div onClick={() => setDeleteModal(review.id)} className="review-delete-btn d-flex align-items-center">
                                        <Icon path={deleteIcon} />
                                        <div className="review-delete-col">
                                          <Heading>Mobile Delete</Heading>
                                        </div>
                                      </div>
                                      <div onClick={() => editReviewPost(review.id)} className="review-edit-btn">
                                        <Button className="btn btn-primary">Mobile Edit</Button>
                                      </div>
                                    </div> */}
                                  </div>
                                )}

                              </div>
                              <div className="bottom-container">
                                <Heading as="h3" class="review-subject">{review.title}</Heading>
                                <div className="review-description" dangerouslySetInnerHTML={{ __html: review.description }} />
                              </div>


                              {(get(Object.values(reviewData)[0], 'images', []).length || get(Object.values(reviewData)[0], 'videos', []).length || get(Object.values(reviewData)[0], 'documents', []).length) ? (
                                <div className="attachment-container">
                                  {(get(Object.values(reviewData)[0], 'images', []).length || get(Object.values(reviewData)[0], 'videos', []).length) ? (
                                    <div className="image-container">
                                      {images && images.length ? (
                                        <div onClick={() => showPreview(true)}>
                                          <Image position="center" img={images[0].post_file_url} round={false} size="auto" extraClass={classes.post_image} loadingClass={classes.post_image_load} />
                                        </div>
                                      ) : null}
                                      {images && (!images.length && videos.length)
                                        ? (
                                          // eslint-disable-next-line jsx-a11y/media-has-caption
                                          <video width="100%" controls>
                                            <source src={videos[0].post_file_url} type={videos[0].metadata.mimeType} />
                                            Your browser does not support the video tag.
                                          </video>
                                        ) : null}
                                    </div>
                                  ) : null}
                                  <AttachmentTile
                                    files={(get(Object.values(reviewData)[0], 'images', []).length || get(Object.values(reviewData)[0], 'videos', []).length) ? allAttachments.slice(1) : allAttachments}
                                    icon={videoImg}
                                    isPreview
                                    showMore
                                  />

                                </div>
                              ) : null}
                            </div>
                            <div className="attendees-container">
                              <Heading as="h6">Attendees:</Heading>
                              <div className="review-chip-col d-flex flex-wrap">
                                {get(reviewData[review.id], 'attendees', []).map(attendee => (
                                  <div className="review-chip-box" key={attendee.id}>
                                    { get(users[attendee.space_user.user_id], 'user.fullname', '')}
                                  </div>
                                ))}
                              </div>
                            </div>
                          </>
                        )
                      }
                    </div>
                  ) : ''}
                </div>
              </MediaQuery>
            </div>
          );
        })}
      </InfiniteScroll>
      {(preview)
      && <DocumentPreview modelProps={{ show: preview, onHide: () => showPreview(false) }} file={get(_values(reviewData)[0], 'images', []).length ? _values(reviewData)[0].images[0] : null} />}
      {deleteModal && (
        <ConfirmationModal
          message="Are you sure you want to delete this review?"
          headerText="Delete review"
          buttonCancel="Cancel"
          modelProps={{ show: !!deleteModal, className: 'sm-popup', onHide: () => setDeleteModal(false) }}
          onSuccess={() => deleteReviewApi()}
          onCancel={() => setDeleteModal(false)}
          buttonText="Delete review"
        />
      )}
      {editReview && (
        <EditReviewModal
          reviewData={editReview}
          onHide={(res) => {
            seteditReview(null);
            if (get(res, 'data', null)) {
              setReviewData({
                [res.data.id]: {
                  ...reviewData[res.data.id],
                  ...res.data,
                },
              });
            }
          }}
        />
      )}
      {!!userInfo && (
        <Modal
          headerText="Business Card"
          onClose={() => {
            setUserInfo(null);
          }}
          visible={!!userInfo}
          modelProps={{ className: 'community-modal', dialogClassName: 'community-member-popup' }}
        >
          <MemberTile member={userInfo} />
        </Modal>
      )}
    </div>
  );
});

AddReview.propTypes = {
  classes: PropTypes.object.isRequired,
};

export default withData(withTheme(injectStyle(styles)(AddReview)));
