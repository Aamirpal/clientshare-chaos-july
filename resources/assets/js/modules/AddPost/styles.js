export const styles = {
  container: {
    background: ({ theme }) => theme.white_color,
    padding: '15px 27px 17px 15px',
    borderRadius: 10,
    border: ({ theme }) => `1px solid ${theme.dark_white}`,
    cursor: 'pointer',
    '@media (max-width: 767px)': {
      borderRadius: 0,
      border: 'none',
    },
  },
  topPanel: {
    display: 'flex',
    alignItems: 'center',
  },
  bottomPanel: {
    display: 'flex',
    alignItems: 'center',
    marginTop: 12,
  },
  postInput: {
    flexGrow: 1,
    border: 'none',
    padding: '0px 14px',
    '&::placeholder': {
      color: ({ theme }) => theme.light_gray,
    },
    '&:disabled': {
      background: ({ theme }) => theme.white_color,
    },
  },
  button: {
    color: '#293248',
    fontWeight: 'normal',
    borderRadius: 18,
    width: 136,
    marginRight: 10,
  },
};
