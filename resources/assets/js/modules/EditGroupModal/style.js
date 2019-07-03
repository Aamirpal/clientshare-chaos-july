import styles from '../GroupTile/styles';

export const style = {
  groupEditMain: {
    position: 'relative',
    marginRight: '10px',
    marginBottom: 11,
    display: ({ theme }) => theme.flex,
    '&:nth-child(5)': {
      marginRight: 0,
    },
  },
  groupEditTile: {
    backgroundColor: ({ theme }) => theme.light_green,
    maxWidth: '132px',
    minWidth: '132px',
    padding: '10px',
    borderRadius: '10px',
    display: 'flex !important',
    flexDirection: 'column',
    justifyContent: 'space-between',
    minHeight: '72px',
    transition: 'all 0.3s ease-in-out',
    position: 'relative',
  },
  editGroupsContainer: {
    marginTop: '0 !important',
  },
  deleteIcon: {
    backgroundColor: ({ theme }) => theme.light_red,
    width: 28,
    height: 28,
    borderRadius: '50%',
    display: ({ theme }) => theme.flex,
    justifyContent: ({ theme }) => theme.center,
    alignItems: ({ theme }) => theme.center,
    position: ({ theme }) => theme.absolute,
    top: -6,
    right: -6,
    cursor: 'pointer',
    '&:hover': {
      backgroundColor: ({ theme }) => theme.dark_red,
    },
  },
  groupLockIcon: {
    marginLeft: 8,
  },
  groupDeleteIcon: {
    marginRight: 8,
    position: ({ theme }) => theme.relative,
    top: -1,
  },
  modalBodyHeading: {
    fontSize: 16,
    lineHeight: 'normal',
    minHeight: 32,
    marginBottom: 27,
  },
  transparentButton: {
    color: ({ theme }) => theme.light_gray,
    fontSize: 13,
    fontWeight: '500',
    lineHeight: '18px',
    backgroundColor: 'transparent',
    cursor: 'pointer',
    padding: '12px 16px 9px 5px',
    '@media (max-width: 767px)': {
      width: '50%',
      textAlign: 'center',
      border: '1px solid #E8F0F8',
      borderRadius: 6,
      padding: '12px 16px 9px 5px',
    },
  },
  redBtn: {
    backgroundColor: ({ theme }) => theme.alert_color,
    fontWeight: '500',
    fontSize: 13,
    lineHeight: '18px',
    padding: '11px 13px 10px 11px',
    borderRadius: 6,
    cursor: 'pointer',
    color: ({ theme }) => theme.white_color,
    '@media (max-width: 767px)': {
      width: '50%',
      textAlign: 'center',
    },
  },
  ...styles,
  modalContainer: {
    padding: '16px 18px 7px 17px',
  },
};
