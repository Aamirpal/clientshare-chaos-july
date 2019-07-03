export const styles = {
  container: ({ theme, disabled }) => ({
    background: theme.white_color,
    padding: `15px ${disabled ? '27px' : '32px'} 17px 15px`,
    borderRadius: 10,
    border: disabled && `1px solid ${theme.dark_white}`,
    cursor: disabled && 'pointer',
    position: 'relative',
  }),
  disabledTopPanel: {
    display: ({ theme }) => theme.flex,
    alignItems: ({ theme }) => theme.center,
  },
  topPanel: {
    display: ({ theme }) => theme.flex,
  },
  bottomPanel: {
    display: ({ theme }) => theme.flex,
    alignItems: ({ theme }) => theme.center,
    marginTop: ({ disabled }) => (disabled ? 12 : 77),
    '@media (max-width: 767px)': {
      marginTop: ({ disabled }) => (disabled ? 12 : 130),
    },
    '@media (max-width: 340px)': {
      marginTop: ({ disabled }) => (disabled ? 12 : 60),
    },
  },
  inputContainer: {
    flexGrow: 1,
    padding: '0 22px',
    '@media (max-width: 767px)': {
      padding: 0,
      margin: '0 8px',
    },
  },
  postInput: {
    border: 'none',
    width: '100%',
    padding: ({ disabled }) => `0px ${disabled ? '0' : '0'}`,
    cursor: 'pointer',
    '@media (max-width: 767px)': {
      fontSize: 14,
      padding: '7.5px 10px !important',
      borderRadius: 18,
      border: '1px solid #E8F0F8',
    },
    '&::placeholder': {
      color: ({ theme }) => theme.light_gray,
      opacity: '1',
    },
    '&:disabled': {
      background: ({ theme }) => theme.white_color,
    },
    '&:focus': {
      outline: 'none',
    },
  },
  postDescription: {
    border: 'none',
    width: '100%',
    overflow: 'hidden',
    resize: 'none',
    lineHeight: '16px',
    color: ({ theme }) => theme.basic_color,
    fontSize: ({ theme }) => theme.normal_font,
    padding: ({ disabled }) => `0px ${disabled ? '14px' : '0'}`,
    '&::placeholder': {
      color: ({ theme }) => theme.light_gray,
      opacity: '1',
    },
    '&:disabled': {
      background: ({ theme }) => theme.white_color,
    },
    '&:focus': {
      outline: 'none',
    },
  },
  subjectInput: {
    fontSize: 18,
    marginBottom: 10,
    lineHeight: '21px',
    color: ({ theme }) => theme.basic_color,
    '@media (max-width: 767px)': {
      paddingLeft: '0 !important',
      paddingBottom: '5px !important',
    },
  },
  button: {
    color: ({ theme }) => theme.basic_color,
    fontWeight: 'normal',
    borderRadius: 18,
    width: 136,
    marginRight: 10,
    '@media (max-width: 767px)': {
      marginRight: 5,
      width: 108,
    },
    '@media (max-width: 340px)': {
      width: 93,
      fontSize: 11,
    },
  },
  filesError: {
    color: ({ theme }) => theme.alert_color,
    fontSize: 12,
    marginLeft: 17,
    marginTop: 10,
    lineHeight: '16px',
  },
  cancelProgress: {
    background: ({ theme }) => theme.light_green,
    borderRadius: 20,
    padding: 8,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
  },
  cancelIcon: {
    width: 10,
  },
  attImage: {
    borderRadius: 4,
    height: 131,
    width: 131,
    objectFit: 'cover',
    objectPosition: 'center',
    '@media (max-width: 767px)': {
      height: 150,
      width: 150,
    },
  },
  attachmentTitle: {
    color: ({ theme }) => theme.light_gray,
    fontSize: 14,
    marginTop: 18,
  },
  singleImage: {
    marginRight: 10,
    position: 'relative',
    margin: '0 10px 5px 0',
  },
  imagesContainer: {
    display: 'flex',
    flexWrap: 'wrap',
  },
  imageDeleteIcon: {
    position: 'absolute',
    top: 10,
    right: 10,
    cursor: 'pointer',
  },
  addPostPopup: {
    maxWidth: 735,
  },
  postBottom: {
    borderTop: ({ theme }) => `1px solid ${theme.dark_white}`,
    padding: '15px 17px',
    '@media (max-width: 767px)': {
      order: 1,
      borderBottom: '1px solid #E8F0F8',
      borderTop: 'none',
    },
  },
  bottomButtonPanel: {
    display: 'flex',
    alignItems: 'center',
    '@media (max-width: 767px)': {
      flexDirection: 'column',
      alignItems: 'flex-start',
    },
  },
  categoryButton: {
    marginLeft: 25,
    color: ({ theme }) => theme.light_gray,
    padding: '6px 8px',
    fontSize: 12,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    cursor: 'pointer',
    minWidth: 94,
    height: 'auto',
    '@media (max-width: 767px)': {
      marginLeft: 0,
      marginRight: 11,
      textAlign: 'left',
    },
  },
  categoryIcon: {
    height: 14,
  },
  groupButton: {
    color: ({ theme }) => theme.primary_color,
    borderRadius: 40,
    padding: '5px 9px',
    fontSize: 12,
    marginLeft: 20,
    display: ({ theme }) => theme.flex,
    alignItems: ({ theme }) => theme.center,
    minWidth: 94,
    justifyContent: ({ theme }) => theme.center,
    height: 'auto',
  },
  showGroupButton: {
    border: ({ theme }) => `1px solid ${theme.primary_color}`,
    boxShadow: '8px 8px 14px rgba(190, 197, 214, 0.1), -8px -8px 14px rgba(190, 197, 214, 0.1)',
  },
  memberContainer: {
    display: 'flex',
    margin: '20px 0 -8px 0',
    flexWrap: 'wrap',
  },
  memberTile: {
    padding: '6px 8px',
    background: ({ theme }) => theme.light_green,
    fontSize: 12,
    display: 'flex',
    justifyContent: 'center',
    marginRight: 10,
    lineHeight: '16px',
    color: ({ theme }) => theme.primary_color,
    borderRadius: 4,
    marginBottom: 10,
  },
  toggleMember: {
    color: ({ theme }) => theme.primary_color,
    margin: '0 10px',
    fontSize: 12,
    cursor: 'pointer',
    fontWeight: '400',
  },
  bottomText: {
    margin: 0,
    fontSize: 14,
    color: ({ theme }) => theme.light_gray,
    '@media (max-width: 767px)': {
      color: '#293248',
      marginBottom: 17,
    },
  },
  buttonTooltip: {
    padding: '10px 7px 12px',
    width: 134,
  },
};
