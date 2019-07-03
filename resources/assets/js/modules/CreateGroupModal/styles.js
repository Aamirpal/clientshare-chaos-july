export default {
  modalContainer: {
    padding: '17px 20px 18px 17px',
  },
  topContainer: ({ theme }) => ({
    display: theme.flex,
    width: '100%',
    marginBottom: '37px',
    alignItems: theme.center,
  }),
  groupInputContainer: ({ theme }) => ({
    flexGrow: 1,
    padding: '0 22px',
    display: theme.flex,
    position: theme.relative,
  }),
  groupInput: {
    border: 'none',
    fontSize: ({ theme }) => theme.medium_font,
    width: '100%',
    '&:focus': {
      outline: 'none',
    },
  },
  groupIconContainer: ({ theme }) => ({
    background: theme.ghost_white,
    width: 60,
    height: 60,
    display: theme.flex,
    alignItems: theme.center,
    justifyContent: theme.center,
    borderRadius: '100px',
  }),
  groupFormContainer: ({ theme }) => ({
    display: theme.flex,
    width: 'calc(100% - 60px)',
  }),
  errorMessage: ({ theme }) => ({
    position: theme.absolute,
    fontSize: '12px',
    lineHeight: '16px',
    color: theme.alert_color,
    top: '42px',
    left: '20px',
  }),
  loader: {
    position: ({ theme }) => theme.absolute,
    zIndex: '99',
    background: 'rgba(255, 255, 255, 0.9)',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    display: ({ theme }) => theme.flex,
    alignItems: ({ theme }) => theme.center,
    justifyContent: ({ theme }) => theme.center,
    borderRadius: 8,
  },
};
