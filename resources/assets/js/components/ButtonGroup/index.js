import React from 'react';
import PropTypes from 'prop-types';
import BaseButtonGroup from 'react-bootstrap/ButtonGroup';
import Button from '../Button';

const ButtonGroup = React.memo(({
  buttons, onClick, active,
}) => (
  <BaseButtonGroup>
    {buttons.map(button => (
      <Button
        buttonProps={{ onClick: () => onClick(button) }}
        key={button.id}
        active={button.id === active}
      >
        {button.name}
      </Button>
    ))}
  </BaseButtonGroup>
));

ButtonGroup.defaultProps = {
  buttons: [],
  onClick: () => { },
  active: '',
};

ButtonGroup.propTypes = {
  buttons: PropTypes.array,
  onClick: PropTypes.func,
  active: PropTypes.string,
};

export default ButtonGroup;
