const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
    ...defaultConfig,
    entry: {
        'text-image': './src/blocks/text-image.js',
        'text': './src/blocks/text.js',
        'team-rollup': './src/blocks/team-rollup.js',
    },
    output: {
        path: __dirname + '/build',    // Output path for compiled files
        filename: '[name].js',         // Compiled file name will match the entry point key
    },
};
