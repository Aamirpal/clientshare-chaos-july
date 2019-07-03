import React from 'react';
import PropTypes from 'prop-types';
import injectSheet from 'react-jss';
import classnames from 'classnames';
import MediaQuery from 'react-responsive';
import { subString } from '../../../utils/methods';
import Image from '../../Image';
import Heading from '../../Heading';
import withTheme from '../../../utils/hoc/withTheme';
import Icon from '../../Icon';

import mailIcon from '../../../images/mail_icon.svg';
import linkedinIcon from '../../../images/linkedin_icon.svg';
import phoneIcon from '../../../images/phone_icon.svg';

const styles = {
  transition: {
    transition: 'all 0.3s ease-in-out 0s',
    '&:hover': {
      boxShadow: ({ theme }) => theme.shadow,
      transform: 'translate(0, -5px)',
    },
  },
};


const MemberTile = React.memo(({
  member, onClick, onKeyPress, animation, classes, showBio,
}) => {
  const companyName = `${member.job_title}, ${member.company.company_name}`;
  return (
    <div
      className={classnames('member-tile', {
        [classes.transition]: animation,
      })}
      onClick={onClick}
      role="button"
      onKeyPress={onKeyPress}
      tabIndex={member.user_id}
    >
      <div className="member-top-row">
        <div className="member-profile-col">
          <Image img={member.profile_image} size={!showBio ? 'extra_large' : 'large'} />
        </div>
        <div className="member-detail-col">
          <Heading as="h4">{`${member.first_name} ${member.last_name}`}</Heading>
          <>
            {showBio ? (
              <span className="small-font">
                {`${subString(companyName, 0, 70)}${companyName.length > 69 ? '...' : ''}`}
              </span>
            ) : (
              <div className="pop-up-designation">
                <span className="small-font">
                  {member.job_title}
                </span>
                <span className="small-font grey-text">
                  {member.company.company_name}
                </span>
              </div>
            )
            }
          </>
          <MediaQuery query="(min-device-width: 767px)">
            {!showBio && (
              <div className="pop-up-social">
                <ul>
                  {member.email && (
                    <li className="email_icon">
                      <a href={`mailto:${member.email}`}>{member.email}</a>
                    </li>
                  )}
                  {member.linkedin_url && (
                    <li className="linkedin_icon">
                      <a target="_blank" href={member.linkedin_url}>{member.linkedin_url}</a>
                    </li>
                  )}
                  {member.contact_number && (
                    <li className="contact_icon">
                      <a href={`tel:${member.contact_number}`}>{member.contact_number}</a>
                    </li>
                  )}
                </ul>
              </div>
            )}
          </MediaQuery>
        </div>
      </div>
      <MediaQuery query="(min-device-width: 767px)">
        {!showBio && (
        <div>
          <div className="community-bio bio-web">
            <p>{member.bio}</p>
          </div>
        </div>
        )}
      </MediaQuery>
      <MediaQuery query="(max-device-width: 767px)">
        <div>
          {!showBio && (
          <>
            <div className="community-bio">
              <p>{member.bio}</p>
            </div>
            <div className="pop-up-social">
              <ul>
                {member.email && (
                <li className="email_icon">
                  <a href={`mailto:${member.email}`}>{member.email}</a>
                </li>
                )}
                {member.linkedin_url && (
                <li className="linkedin_icon">
                  <a target="_blank" href={`${member.linkedin_url}`}>{member.linkedin_url}</a>
                </li>
                )}
                {member.contact_number && (
                <li className="contact_icon">
                  <a href={`tel:${member.contact_number}`}>{member.contact_number}</a>
                </li>
                )}
              </ul>
            </div>
          </>
          )}
        </div>
      </MediaQuery>
      {showBio && (
      <>
        <div className="community-body-row">
          <Heading>{`${subString(member.bio, 0, 70)}${member.bio.length > 69 ? '...' : ''}`}</Heading>
        </div>
        <div className="community-footer">
          <ul>
            <li>
              <span>
                <Icon path={mailIcon} />
              </span>
            </li>
            {member.linkedin_url && (
            <li>
              <span>
                <Icon path={linkedinIcon} />
              </span>
            </li>
            )}
            {member.contact_number && (
            <li className="phone-icon">
              <span>
                <Icon path={phoneIcon} />
              </span>
            </li>
            )}
          </ul>
        </div>
      </>
      )}
    </div>
  );
});

MemberTile.propTypes = {
  member: PropTypes.object.isRequired,
  classes: PropTypes.object.isRequired,
  animation: PropTypes.bool,
  onClick: PropTypes.func,
  onKeyPress: PropTypes.func,
  showBio: PropTypes.bool,
};

MemberTile.defaultProps = {
  onClick: () => {},
  onKeyPress: () => {},
  animation: false,
  showBio: false,
};

export default withTheme(injectSheet(styles)(MemberTile));
