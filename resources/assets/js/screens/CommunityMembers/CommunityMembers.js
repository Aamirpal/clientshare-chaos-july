import React from 'react';
import InfiniteScroll from 'react-infinite-scroller';

/** api , methods */
import { getCommunityMembers, getCommunitySpaceInfo } from '../../api/app';
import { globalConstants } from '../../utils/constants';
import { createMemberTabData, getWindowHeight } from '../../utils/methods';
/** Components */
import ButtonGroup from '../../components/ButtonGroup';
import { MemberTile, TileWrapper } from '../../components/Tile';
import Modal from '../../components/Modal';
import ContentLoader from '../../components/ContentLoader';
import Icon from '../../components/Icon';
import Heading from '../../components/Heading';
import addIcon from '../../images/add_icon.png';
import closeIcon from '../../images/close_bg_icon.svg';


class CommunityMembers extends React.PureComponent {
    state ={
      communityMembers: [],
      tabData: [],
      activeCompany: null,
      showMemberInfo: false,
      selectedMember: null,
      hasMoreMembers: true,
      pagination: {
        limit: 20,
        page: 1,
      },
    }

    apicall = false;

    componentDidMount() {
      this.getCommunitySpaceInfoData();
    }

    getCommunityMembersData = () => {
      if (!this.apicall) {
        this.apicall = true;
        let { communityMembers } = this.state;
        const { pagination, activeCompany } = this.state;
        const { page, limit } = pagination;
        const { clientShareId } = globalConstants;
        const offset = (page - 1) * limit;
        getCommunityMembers(clientShareId, activeCompany, limit, offset)
          .then(({ data }) => {
            const { community_members } = data;
            pagination.page += 1;
            communityMembers = [
              ...communityMembers,
              ...community_members,
            ];
            this.apicall = false;
            this.setState(() => ({
              communityMembers,
              hasMoreMembers: community_members.length !== 0,
              pagination,
            }));
          }).catch(() => {});
      }
      return false;
    }

    getCommunitySpaceInfoData = () => {
      const { clientShareId } = globalConstants;
      getCommunitySpaceInfo(clientShareId).then(({ data }) => {
        const tabData = createMemberTabData(data);
        this.setState(() => ({
          tabData,
        }));
      }).catch(() => {});
    }

    /** Filter Tabs / Initialize Page/ Empty Community Members */
    filterData = ({ id }) => {
      const { pagination } = this.state;
      pagination.page = 1;
      this.setState(() => ({
        activeCompany: id,
        communityMembers: [],
        pagination,
      }), () => {
        this.setState(() => ({
          hasMoreMembers: true,
        }));
      });
    }

    selectMember = selectedMember => this.setState(() => ({ selectedMember, showMemberInfo: true }))


    closeMemberInfo = () => this.setState(() => ({ showMemberInfo: false }))

    render() {
      const {
        communityMembers,
        tabData,
        activeCompany,
        showMemberInfo,
        selectedMember,
        hasMoreMembers,
      } = this.state;

      return (
        <div className="container-fluid">
          <div className="row justify-content-center col py-3 px-lg-5 hidden-mbl">
            <ButtonGroup buttons={tabData} onClick={this.filterData} active={activeCompany} />
          </div>
          <div className="community-search hidden">
            <div className="search-mbl-box">
              <form className="mobile-search-form">
                <input autoComplete="off" spellCheck="false" className="form-control search-box" type="search" placeholder="Search members..." />
                <Icon path={closeIcon} />
              </form>
            </div>
          </div>
          <div className="community-member-row">
            <InfiniteScroll
              pageStart={0}
              loadMore={this.getCommunityMembersData}
              hasMore={hasMoreMembers && !this.apicall}
              loader={null}
              className="row"
              threshold={getWindowHeight(2)}
            >
              <div className="custom-col">
                <TileWrapper color="light_green">
                  <Icon path={addIcon} />
                  <Heading as="h4">Add a new member</Heading>
                </TileWrapper>
              </div>
              {communityMembers.map(member => (
                <div className="custom-col" key={member.user_id}>
                  <MemberTile
                    member={member}
                    onClick={() => this.selectMember(member)}
                    animation
                    showBio
                  />
                </div>
              ))}
              {hasMoreMembers && <ContentLoader items={3} height={250} col={3} key={0} className="custom-col" />}

            </InfiniteScroll>
          </div>
          <Modal headerText="Business Card" onClose={this.closeMemberInfo} visible={showMemberInfo} modelProps={{ dialogClassName: 'community-member-popup', className: 'community-modal' }}>
            {selectedMember && <MemberTile member={selectedMember} />}
          </Modal>
        </div>
      );
    }
}

export default CommunityMembers;
