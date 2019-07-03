import React, { useState } from 'react';
import propTypes from 'prop-types';
import { toast } from 'react-toastify';
import MediaQuery from 'react-responsive';
import FeedHeader from '../../modules/FeedHeader';
import AddReview from '../../modules/AddReview';
import Heading from '../../components/Heading';
import Icon from '../../components/Icon';
import BusinessIcon from '../../images/business-reviews.svg';
import documentIcon from '../../images/document-icon.svg';
import { reviewList } from '../../api/app';
import { BusinessReviewContext } from '../../utils/contexts/index';
import BusinessReviewsModal from './AddReviewModal';
import './business_review.scss';

const BusinessReviews = React.memo(({ allowCurrentUserPost }) => {
  const [reviewListShow, setReviewListShow] = useState({
    reviews: [],
    offset: 0,
    loaded: false,
  });
  const [hasMoreReviews, setHasMoreReviews] = useState(true);

  const getReviewList = (index, added = false) => {
    if (added) {
      setReviewListShow(prev => ({
        ...prev,
        reviews: [],
        offset: 0,
      }));
      return setHasMoreReviews(true);
    }

    return reviewList(reviewListShow.offset).then(({ data: { business_review, offset } }) => {
      setReviewListShow(prev => ({
        ...prev,
        reviews: [
          ...prev.reviews,
          ...business_review,
        ],
        offset,
        loaded: true,
      }));
      if (business_review.length > 7) {
        setHasMoreReviews(true);
      } else {
        setHasMoreReviews(false);
      }
    }).catch(() => toast.error('Post Not Found'));
  };

  const updateReviews = (updateReviewData) => {
    setReviewListShow(prev => ({
      ...prev,
      reviews: [
        ...updateReviewData,
      ],
    }));
  };
  const { reviews, loaded } = reviewListShow;
  return (
    <BusinessReviewContext.Provider value={{
      reviewLoadData: reviewListShow,
      fetchReviews: getReviewList,
      updateReviews,
      hasMoreReviews,
    }}
    >
      <div className="feed-post-wrap business-post">
        <div className="feed-right-part flex-column">
          <MediaQuery query="(min-width: 767px)">
            <div className="business-head-col w-100 d-flex justify-content-between">
              <FeedHeader text="Business Reviews" icon={BusinessIcon} />
              <div className="review-post-count d-flex align-items-center">
                <Heading>
                  {reviewListShow.reviews.length}
                  {' '}
                  posts
                </Heading>
                <Icon path={documentIcon} />
              </div>
            </div>
          </MediaQuery>
          {Boolean(!reviews.length && loaded) && <div className="no-review-text">There are no posts in this category at the moment. </div>}
          {allowCurrentUserPost && (
          <BusinessReviewsModal />
          )}
          <AddReview />
        </div>
      </div>
    </BusinessReviewContext.Provider>
  );
});

BusinessReviews.propTypes = {
  allowCurrentUserPost: propTypes.bool.isRequired,
};

export default BusinessReviews;
