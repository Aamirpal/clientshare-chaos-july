import React, { PureComponent } from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import _values from 'lodash/values';
import _keys from 'lodash/keys';
import Slider from 'react-slick';
import Spinner from 'react-bootstrap/Spinner';
import { Scrollbars } from 'react-custom-scrollbars';
import withTheme from '../../utils/hoc/withTheme';
import withPostData from '../../utils/hoc/withPostData';
import { updateMemberData } from '../../utils/methods';
import { UpdateGroupContext } from '../../utils/contexts';
import { getGroupMembers } from '../../api/app';
import Heading from '../../components/Heading';
import CustomIcons from '../../components/CustomIcons';
import styles from './styles';
import Arrow from './Arrow';
import { Icon, Modal } from '../../components';
import profileAvatar from '../../images/user-placeholder.svg';

class GroupTile extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      displayLeftArrow: false,
      displayRightArrow: true,
      memberList: {},
      memberListShow: false,
      selectedGroup: false,
    };
    this.slidesToShow = 6;
    this.sliderSetting = {
      dots: false,
      arrows: true,
      infinite: false,
      className: 'groups-slider',
      initialSlide: 0,
      speed: 1000,
      slidesToScroll: 6,
      slidesToShow: this.slidesToShow,
      nextArrow: null,
      prevArrow: null,
      afterChange: (currentSlide) => {
        this.setArrowDisplay(currentSlide);
      },
      responsive: [
        {
          breakpoint: 1300,
          settings: {
            slidesToShow: 5,
            slidesToScroll: 5,
          },
        },
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 4,
          },
        },
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3,
          },
        },
      ],
    };

    this.slider = React.createRef();
  }

clickHandler = (direction) => {
  if (this.slider.currentSlide) {
    if (direction === 'left') {
      this.slider.slickPrev();
    } else if (direction === 'right') {
      this.slider.slickNext();
    }
  }
};

setArrowDisplay = (currentSlide) => {
  const { groups } = this.props;
  const displayLeftArrow = currentSlide !== 0;
  let displayRightArrow = true;
  if (((_values(groups).length - currentSlide) + 1) <= 6) {
    displayRightArrow = false;
  }
  this.setState({ displayRightArrow, displayLeftArrow });
};

getMemberList = (groupId) => {
  let { memberList } = this.state;
  this.setState(() => ({ memberListShow: true }));
  getGroupMembers(groupId).then((res) => {
    memberList = updateMemberData(res, memberList, groupId);
    this.setState(() => ({ memberList, selectedGroup: groupId }));
  });
}

render() {
  const {
    classes, groups, createGroupButton, allowUserPost, postData: { space_groups },
  } = this.props;
  const {
    displayRightArrow,
    displayLeftArrow,
    memberList,
    memberListShow,
    selectedGroup,
  } = this.state;

  const filterGroups = _values(groups).filter(group => _keys(space_groups).includes(String(group.id)));
  return (
    <div className={classnames(classes.groupsContainer, 'groups-slider-container')}>
      <UpdateGroupContext.Consumer>
        {({ updateGroup, groupId }) => (
          <>
            {_values(filterGroups).length > 6
        && (
        <div>
          <Arrow
            styleClassName={`slider-arrow-right ${
              displayRightArrow ? '' : 'hide'
            }`}
            direction="right"
            clickHandler={this.clickHandler}
          />
          <Arrow
            styleClassName={`slider-arrow-left ${
              displayLeftArrow ? '' : 'hide'
            }`}
            direction="left"
            clickHandler={this.clickHandler}
          />
        </div>
        )
        }

            <Slider ref={this.slider} {...this.sliderSetting}>
              {(_values(filterGroups).length > 1 || allowUserPost) && _values(filterGroups).map(groupsContent => (
                <div
                  className={classnames(classes.groupTile, {
                    [classes.activeTile]: groupId === groupsContent.id,
                  })}
                  key={groupsContent.id}
                  onClick={() => updateGroup(groupsContent.id)}
                >
                  <Heading
                    as="h5"
                    headingProps={{
                      className: classnames(classes.groupHeading, {
                        [classes.activeGroupHeading]: groupId === groupsContent.id,
                      }),
                    }}
                  >
                    {groupsContent.name}
                  </Heading>

                  {groupsContent.is_default ? (
                    <div className={classnames(classes.memberCount, classes.allMemberCount, 'member-count', {
                      [classes.activeMemberCount]: groupId === groupsContent.id,
                    })}
                    >
                      <span>all</span>
                      <CustomIcons
                        icon="globe"
                        iconProps={{
                          className: classnames(classes.customIcon, {
                            [classes.activeCustomIcon]: groupId === groupsContent.id,
                          }),
                        }}
                      />
                    </div>
                  ) : (
                    <div className={classnames(classes.memberCount, 'member-count', {
                      [classes.activeMemberCount]: groupId === groupsContent.id,
                    })}
                    >
                      <span key={groupsContent.id} onClick={() => this.getMemberList(groupsContent.id)}>
                        {groupsContent.group_users_count}
                        {' '}
                  member
                        {`${groupsContent.group_users_count > 1 ? 's' : ''}`}
                      </span>
                      <CustomIcons
                        icon="lock"
                        iconProps={{
                          className: classnames(classes.customIcon, {
                            [classes.activeCustomIcon]: groupId === groupsContent.id,
                          }),
                        }}
                      />
                    </div>
                  )}
                </div>
              ))}

              {(_values(groups).length <= 1 && allowUserPost)
              && (
              <div className={classes.createGroupTile} onClick={createGroupButton}>
                <Heading as="h5" headingProps={{ className: classes.createGroupHeading }}> Create a group </Heading>
                <p className={classes.memberCount}>
                  <CustomIcons icon="addIconSmall" iconProps={{ className: classes.customIcon }} />
                </p>
              </div>
              )
          }
            </Slider>
          </>
        )}

      </UpdateGroupContext.Consumer>


      <Modal modelProps={{ className: 'sm-popup member-list-popup', show: memberListShow }} headerText="Members" onClose={() => { this.setState({ memberListShow: false, memberList: {} }); }}>
        <div className={classes.modalContainer}>
          <div className={classes.memberListWrap}>
            <Scrollbars autoHeight autoHeightMax={320} autoHeightMin={160}>
              {selectedGroup && memberList[selectedGroup] && memberList[selectedGroup].show ? (memberList[selectedGroup].data.map(groupMembers => (
                <div className={classes.memberList}>
                  <span className={classes.memberImage}>
                    {groupMembers.circular_profile_image ? <Icon path={groupMembers.circular_profile_image} /> : <Icon path={profileAvatar} />}
                  </span>
                  <div className={classes.memberDetails}>
                    <Heading as="h5" headingProps={{ className: classes.memberName }}>{groupMembers.full_name}</Heading>
                    <p>{groupMembers.company_name}</p>
                  </div>
                </div>
              ))) : (<div className="loader d-flex w-100 justify-content-center align-items-center"><Spinner animation="border" variant="success" /></div>) }
            </Scrollbars>
          </div>
        </div>
      </Modal>
    </div>
  );
}
}

GroupTile.propTypes = {
  classes: PropTypes.object.isRequired,
  groups: PropTypes.object.isRequired,
  createGroupButton: PropTypes.func.isRequired,
  allowUserPost: PropTypes.bool.isRequired,
};

export default withPostData(withTheme(injectSheet(styles)(GroupTile)));
