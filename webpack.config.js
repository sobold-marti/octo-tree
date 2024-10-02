const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
    ...defaultConfig,
    entry: {
        'text-image': './src/blocks/text-image.js', // Block 1 entry point
    },
    output: {
        path: __dirname + '/build',    // Output path for compiled files
        filename: '[name].js',         // Compiled file name will match the entry point key
    },
};
