import React, { PureComponent } from 'react';
import { ToastContainer, toast } from 'react-toastify';
import _values from 'lodash/values';
import 'react-toastify/dist/ReactToastify.css';
import StickyBox from 'react-sticky-box';

import { CategoryContext, UpdateCategoryContext, UsersContext } from '../../utils/contexts/index';
import {
  getCommunityMember, getSpaceCategories, getAllUsers, shareProfileProgress,
} from '../../api/app';
import { globalConstants } from '../../utils/constants';
import { getAllUrlParams } from '../../utils/methods';
import {
  Tile, Button, Image, ContentLoader, Heading,
} from '../../components';
import CategoryTile from '../../components/Tile/CategoryTile';
import FeedRight from './FeedRight';
import LockSvg from '../../images/lock_community.svg';

export default class Feed extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      communityData: {},
      categories: {},
      users: {},
      isLoaded: false,
      activeCategory: 0,
      businessReviews: false,
      shareProgress: {},
    };
  }

  componentDidMount() {
    this.getCommunityMembersData();
    this.getCategoriesData();
    this.storeUsers();
    shareProfileProgress().then(({ data }) => this.setState({ shareProgress: data })).catch(() => false);
  }

  getCommunityMembersData = () => {
    const { clientShareId } = globalConstants;
    getCommunityMember(clientShareId).then(({ data }) => {
      this.setState({ communityData: data, isLoaded: true });
    }).catch(() => {
      toast.error('Something went wrong');
    });
  }

  getCategoriesData = () => {
    const { clientShareId } = globalConstants;
    getSpaceCategories(clientShareId).then(({ data: { space_categories } }) => {
      if (getAllUrlParams().type === 'review') {
        const businessReviewsData = _values(space_categories).find(item => item.category_name === 'Business Reviews');
        this.selectCategory(businessReviewsData);
      }
      this.setState(() => ({ categories: space_categories }));
    }).catch(() => {
      toast.error('Something went wrong');
    });
  }

  selectCategory = ({ category_id, category_name }) => {
    if (category_name !== 'Business Reviews') {
      const { clientShareId } = globalConstants;
      window.history.replaceState({}, document.title, `/clientshare/${clientShareId}`);
      this.setState(() => ({ activeCategory: category_id, businessReviews: false }));
    } else {
      this.setState(() => ({ activeCategory: category_id, businessReviews: true }));
    }
  }

  storeUsers = () => {
    getAllUsers().then(({ data }) => {
      this.setState({ users: data });
    }).catch(() => false);
  }

  updateUsers = () => {
    // User Update
  }

  render() {
    const {
      communityData, categories, isLoaded, activeCategory, businessReviews, users, shareProgress: { progress },
    } = this.state;
    const { clientShareId } = globalConstants;
    return (
      <UsersContext.Provider value={{
        users,
        updateUsers: this.updateUsers,
      }}
      >
        <CategoryContext.Provider value={categories}>
          <UpdateCategoryContext.Provider value={{
            updateCategory: this.selectCategory,
            categoryId: activeCategory,
          }}
          >
            <div className="feedpage-main">
              <StickyBox className="categories-wrap" offsetTop={56} offsetBottom={20}>
                <div className="categories-container">

                  {!isLoaded && (<ContentLoader className="category-tile" items={6} height={100} />)}
                  {_values(categories).map(category => (
                    <CategoryTile
                      key={category.category_id}
                      category={category}
                      active={activeCategory}
                      onClick={() => this.selectCategory(category)}
                    />
                  ))}
                </div>
                <div className="invite-col">
                  {Number(progress) < 99 ? (
                    <div className="community-lock-container">
                      <div>
                        <Heading as="h3" headingProps={{ className: 'community-title' }}>My Community</Heading>
                        <Heading as="h4" headingProps={{ className: 'community-description' }}>
                        Complete the setup tasks before inviting your community
                        </Heading>
                      </div>
                      <div className="community-lock-image-container">
                        <Image img={LockSvg} round={false} size="img31" />
                      </div>

                    </div>
                  ) : (
                    <Tile members={communityData.users_count} whitecolor="white" heading="My Community" withButton goto={`/community_members/${clientShareId}`}>
                      <div className="invite-button">
                        <Button>Invite</Button>
                      </div>
                      <div className="user-profile-row">
                        {communityData.users_preview_list && communityData.users_preview_list.map((user, index) => (
                          <Image key={index} img={user.profile_image_url} size="small" />
                        ))}
                      </div>
                      <div className="link-text">
                        <span>View all members</span>
                      </div>
                    </Tile>
                  )}

                </div>
                <ToastContainer position="bottom-right" />
              </StickyBox>
              <FeedRight businessReviews={businessReviews} />
            </div>
          </UpdateCategoryContext.Provider>
        </CategoryContext.Provider>
      </UsersContext.Provider>
    );
  }
}
