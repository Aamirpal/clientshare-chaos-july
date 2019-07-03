export const styles = {
  tagContainer: {
    border: ({ theme }) => `1px solid ${theme.dark_white}`,
    borderRadius: '4px',
    padding: '0 12px',
    position: 'relative',
  },
  tagInput: {
    height: 47,
    fontSize: 14,
    border: '0',
    padding: '11px 0 12px',
    '&::-moz-placeholder': {
      fontSize: '14px',
      color: '#748AA1',
    },
    '&:-ms-input-placeholder': {
      fontSize: '14px',
      color: '#748AA1',
    },
    '&::-webkit-input-placeholder': {
      fontSize: '14px',
      color: '#748AA1',
    },
    '&::placeholder': {
      fontSize: '14px',
      color: '#748AA1',
    },
  },
  listUsers: {
    width: 181,
    position: 'absolute',
    left: '0',
    borderRadius: 10,
    top: '46px',
    '@media (max-width: 767px)': {
      zIndex: 9,
    },
    '&:before': {
      borderBottom: ({ theme }) => `10px solid ${theme.ghost_white}`,
      borderLeft: '10px solid transparent',
      borderRight: '10px solid transparent',
      content: '""',
      height: 0,
      left: 14,
      position: 'absolute',
      right: 0,
      width: 0,
      zIndex: 9999,
      top: -9,
    },
  },
  listUsersInner: {
    background: ({ theme }) => theme.white_color,
    border: ({ theme }) => `1px solid ${theme.dark_white}`,
    borderRadius: 10,
    overflow: 'hidden',
    '@media (max-width: 767px)': {
      maxHeight: '150px',
    },
  },
  listItem: {
    padding: '7px 9px 6px 9px',
    borderBottom: ({ theme }) => `1px solid ${theme.dark_white}`,
    '&:hover': {
      background: ({ theme }) => theme.ghost_white,
      cursor: 'pointer',
    },
  },
  user_name: {
    margin: '0 0 2px',
  },
  company_name: {
    color: ({ theme }) => theme.light_gray,
    fontSize: 12,
    fontWeight: 'normal',
    margin: '0',
    lineHeight: 'normal',
  },
  chip: {
    display: 'flex',
    flexWrap: 'wrap',
  },
  chipItem: {
    display: 'flex',
    padding: '7px 8px 7px 7px',
    background: ({ theme }) => theme.light_green,
    marginRight: 8,
    marginBottom: 10,
    borderRadius: 4,
    alignItems: 'center',
  },
  tagName: {
    fontWeight: 'normal',
    color: ({ theme }) => theme.primary_color,
    marginBottom: 0,
    marginRight: 5,
    fontSize: '13px',
  },
  deleteIcon: {
    cursor: 'pointer',
    width: '12px',
    height: '12px',
  },
  icon: {
    width: '100%',
  },
  errorMessage: {
    fontSize: '12px',
    lineHeight: '16px',
    color: '#FF647C',
    marginTop: '6px',
  },
};
