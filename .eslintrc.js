module.exports = {
  env: {
    browser: true,
    es6: true,
    jest: true,
  },
  "parser": "babel-eslint",
  extends: 'airbnb',
  globals: {
    Atomics: 'readonly',
    SharedArrayBuffer: 'readonly',
  },
  parserOptions: {
    ecmaFeatures: {
      jsx: true,
    },
    ecmaVersion: 2018,
    sourceType: 'module',
  },
  plugins: [
    'react',
  ],
  rules: {
    "react/jsx-filename-extension": [1, { "extensions": [".js", ".jsx"] }],
    "react/forbid-prop-types": 0,
    "react/no-typos": 0,
    "import/prefer-default-export": 0,
    'camelcase': 0,
    'jsx-a11y/interactive-supports-focus': 0,
    'jsx-a11y/click-events-have-key-events': 0,
    'jsx-a11y/no-static-element-interactions': 0,
    'react/no-array-index-key': 0,
    'import/no-cycle':0,
    'jsx-a11y/no-noninteractive-element-interactions': 0,
  },
};
