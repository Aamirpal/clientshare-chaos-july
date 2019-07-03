import React, { useContext } from 'react';
import _values from 'lodash/values';
import get from 'lodash/get';
import { Scrollbars } from 'react-custom-scrollbars';
import injectStyle from 'react-jss';
import withTheme from '../../../utils/hoc/withTheme';
import { Modal, Heading, Image } from '../../../components';
import { UsersContext } from '../../../utils/contexts';

const styles = {
  memberList: {
    display: ({ theme }) => theme.flex,
    borderBottom: '1px solid #E8F0F8',
    padding: '16px',
    alignItems: ({ theme }) => theme.center,
    '&:last-child': {
      border: 'none',
    },
  },
  memberImage: {
    marginRight: 16,
  },
  memberName: {
    fontSize: 16,
    lineHeight: '19px',
    color: ({ theme }) => theme.basic_color,
    marginBottom: 8,
  },
  companyName: {
    fontSize: 14,
    lineHeight: '22px',
    color: ({ theme }) => theme.light_gray,
    margin: 0,
  },
};

const UsersModal = ({
  modalProps, users, classes, title,
}) => {
  const usersContext = useContext(UsersContext);
  const allusers = usersContext.users;
  return (
    <Modal modelProps={modalProps} headerText={title}>
      <div className={classes.modalContainer}>
        <div className={classes.memberListWrap}>
          <Scrollbars autoHeight autoHeightMax={328} autoHeightMin={82}>
            {_values(users).map(user => (
              <div className={classes.memberList} key={user.user_id}>
                <div className={classes.memberImage}>
                  <Image img={get(allusers[user.user_id], 'user.circular_image_url', null)} size="medium" />
                </div>
                <div className={classes.memberDetails}>
                  <Heading as="h5" headingProps={{ className: classes.memberName }}>{get(allusers[user.user_id], 'user.fullname')}</Heading>
                  <Heading headingProps={{ className: classes.companyName }}>{get(allusers[user.user_id], 'company.company_name')}</Heading>
                </div>
              </div>
            ))}
          </Scrollbars>
        </div>
      </div>
    </Modal>
  );
};

export default withTheme(injectStyle(styles)(UsersModal));
