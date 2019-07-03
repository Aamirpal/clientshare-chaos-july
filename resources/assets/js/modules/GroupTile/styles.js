
export default {
  groupsContainer: {
    display: ({ theme }) => theme.flex,
    flexWrap: 'wrap',
    width: '100%',
    maxWidth: '850px',
    margin: '20px 0',
    position: ({ theme }) => theme.relative,
    '@media (max-width: 767px)': {
      display: 'none !important',
    },
  },
  groupTile: {
    backgroundColor: ({ theme }) => theme.light_green,
    maxWidth: '132px',
    minWidth: '132px',
    padding: '10px',
    borderRadius: '10px',
    display: 'flex !important',
    flexDirection: 'column',
    justifyContent: 'space-between',
    minHeight: '72px',
    position: ({ theme }) => theme.relative,
    marginRight: '10px',
    cursor: 'pointer',
    transition: 'all 0.2s ease-in-out',
    '&:hover': {
      backgroundColor: ({ theme }) => theme.primary_color,
      '& h5': {
        color: ({ theme }) => theme.white_color,
      },
      '& .member-count': {
        color: ({ theme }) => theme.white_color,
        '& path': {
          fill: ({ theme }) => theme.white_color,
        },
      },
      '& p': {
        color: ({ theme }) => theme.white_color,
        '& path': {
          fill: ({ theme }) => theme.white_color,
        },
      },
    },
  },
  createGroupTile: {
    backgroundColor: ({ theme }) => theme.dusky_gray,
    maxWidth: '132px',
    minWidth: '132px',
    padding: '10px',
    borderRadius: '10px',
    display: 'flex !important',
    flexDirection: 'column',
    justifyContent: 'space-between',
    minHeight: '72px',
    position: ({ theme }) => theme.relative,
    marginRight: '10px',
    cursor: 'pointer',
  },
  groupHeading: ({ theme }) => ({
    fontWeight: 500,
    fontSize: theme.normal_font,
    lineHeight: 'normal',
    color: theme.primary_color,
    margin: 0,
    paddingRight: 8,
    wordBreak: 'break-word',
    wordWrap: 'break-word',
  }),
  createGroupHeading: ({ theme }) => ({
    color: theme.light_gray,
    fontWeight: 500,
    fontSize: theme.normal_font,
    lineHeight: 'normal',
    margin: 0,
    paddingRight: 8,
    wordBreak: 'break-word',
  }),
  memberCount: {
    fontWeight: 'normal',
    fontSize: ({ theme }) => theme.normal_font - 2,
    lineHeight: 'normal',
    textAlign: 'right',
    color: ({ theme }) => theme.primary_color,
    margin: 0,
    display: ({ theme }) => theme.flex,
    alignItems: ({ theme }) => theme.center,
    justifyContent: 'flex-end',
    position: ({ theme }) => theme.absolute,
    right: '10px',
    bottom: '10px',
    '& span': {
      '&:hover': {
        textDecoration: 'underline',
      },
    },
  },
  allMemberCount: {
    '& span': {
      '&:hover': {
        textDecoration: 'none',
      },
    },
  },
  icon: {
    marginLeft: '6px',
  },
  customIcon: {
    marginLeft: '6px',
  },
  activeTile: {
    background: ({ theme }) => theme.primary_color,
  },
  activeGroupHeading: ({ theme }) => ({
    color: theme.white_color,
  }),
  activeMemberCount: ({ theme }) => ({
    color: theme.white_color,
  }),
  activeCustomIcon: {
    '& path': {
      fill: ({ theme }) => theme.white_color,
    },
  },
  modalContainer: {
    height: 'auto',
  },
  memberListWrap: {
    height: 'auto',
  },
  memberList: {
    display: ({ theme }) => theme.flex,
    borderBottom: '1px solid #E8F0F8',
    padding: '12px 15px',
    alignItems: ({ theme }) => theme.center,
  },
  memberImage: {
    width: '54px',
    height: '54px',
    display: ({ theme }) => theme.flex,
    background: ({ theme }) => theme.light_gray,
    borderRadius: ({ theme }) => theme.full_width,
    marginRight: '14px',
    '& img': {
      width: '100%',
      transform: 'scale(1.1)',
    },
  },
  memberDetails: {
    '& p': {
      fontWeight: 'normal',
      fontSize: '12px',
      lineHeight: '14px',
      color: ({ theme }) => theme.light_gray,
      margin: '0',
    },
  },
  memberName: {
    fontWeight: '500',
    fontSize: '16px',
    lineHeight: '19px',
    color: ({ theme }) => theme.basic_color,
    marginBottom: '7px',
  },
};
