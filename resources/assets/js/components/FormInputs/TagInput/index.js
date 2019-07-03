import React from 'react';
import Form from 'react-bootstrap/Form';
import get from 'lodash/get';
import classnames from 'classnames';
import PropTypes from 'prop-types';
import find from 'lodash/find';
import injectSheet from 'react-jss';
import uuidv4 from 'uuid/v4';
import { Scrollbars } from 'react-custom-scrollbars';

import { UsersContext } from '../../../utils/contexts';
import CloseIcon from '../../../images/close-icon.svg';
import Icon from '../../Icon';
import Heading from '../../Heading';
import withTheme from '../../../utils/hoc/withTheme';
import { globalConstants } from '../../../utils/constants';
import { styles } from './styles';

const { userId } = globalConstants;

const TagInput = ({
  classes, inputProps, users, selectedMembers, onSelect, onRemove, showApiError, showMemberList,
}) => (
  <div>
    <div className={classnames(classes.tagContainer, 'tag-container')}>
      {showMemberList && users.length ? (
        <div className={classnames(classes.listUsers, 'list-users')}>
          <div className={classes.listUsersInner}>
            <UsersContext.Consumer>
              {values => (
                <Scrollbars autoHeight autoHeightMax={246}>
                  {users.map(user => (
                    !find(selectedMembers, user) && (
                    <div className={classes.listItem} onClick={() => onSelect(user)} key={`user-${uuidv4()}`}>
                      <Heading as="h4" headingProps={{ className: classes.user_name }}>{user.full_name}</Heading>
                      <Heading headingProps={{ className: classes.company_name }}>{get(values, `users.${user.user_id}.company.company_name`, '')}</Heading>
                    </div>
                    )
                  ))}
                </Scrollbars>
              )}
            </UsersContext.Consumer>
          </div>
        </div>
      ) : ''}
      <div className={classnames(classes.chip, 'tag-chip')}>
        <Form.Control
          className={classnames(classes.tagInput, 'tag-input')}
          size="lg"
          type="text"
          placeholder="Add user starting from @"
          autoComplete="off"
          spellCheck="false"
          {...inputProps}
        />
        {selectedMembers.map(member => (
          <div key={`member-${uuidv4()}`} className={classnames(classes.chipItem, 'tag-chipItem')}>
            <Heading as="h6" headingProps={{ className: classes.tagName }}>{member.full_name}</Heading>
            {userId !== member.user_id
            && (
            <div onClick={() => onRemove(member)} className={classes.deleteIcon}>
              <Icon path={CloseIcon} iconProps={{ className: classes.icon }} />
            </div>
            )}


          </div>
        ))}
      </div>
    </div>
    {get(showApiError, 'user_ids', false) && <div className={classes.errorMessage}>{showApiError.user_ids[0]}</div>}
  </div>
);

TagInput.propTypes = {
  classes: PropTypes.object.isRequired,
  inputProps: PropTypes.object.isRequired,
  users: PropTypes.any.isRequired,
  onSelect: PropTypes.func.isRequired,
  selectedMembers: PropTypes.array.isRequired,
  onRemove: PropTypes.func.isRequired,
  showApiError: PropTypes.any,
  showMemberList: PropTypes.any.isRequired,
};

TagInput.defaultProps = {
  showApiError: false,
};

export default withTheme(injectSheet(styles)(TagInput));
