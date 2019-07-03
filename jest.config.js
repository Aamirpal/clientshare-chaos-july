module.exports = {
  // Automatically clear mock calls and instances between every test
  clearMocks: true,

  collectCoverageFrom: ['resources/assets/js/src/**/*.{js,jsx,mjs}'],

  // The directory where Jest should output its coverage files
  coverageDirectory: 'coverage',

  // An array of file extensions your modules use
  moduleFileExtensions: ['js', 'json', 'jsx'],

  setupFiles: ['<rootDir>/enzyme.config.js'],

  // The test environment that will be used for testing
  testEnvironment: 'jsdom',

  // The glob patterns Jest uses to detect test files
  testMatch: ['<rootDir>resources/assets/js/**/__tests__/**/*.js?(x)', '<rootDir>resources/assets/js/**/?(*.)+(spec|test).js?(x)'],

  testPathIgnorePatterns: ['\\\\node_modules\\\\'],

  testURL: 'http://localhost',

  transformIgnorePatterns: ['<rootDir>/node_modules/'],

  // Indicates whether each individual test should be reported during the run
  verbose: false,

  transform: {
    '^.+\\.jsx?$': 'babel-jest',
    '\\.(jpg|jpeg|png|gif|eot|otf|webp|svg|ttf|woff|woff2|mp4|webm|wav|mp3|m4a|aac|oga|scss|css)$':
      '<rootDir>/svgTransform.js',
  },
};
