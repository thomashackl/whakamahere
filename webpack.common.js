const webpack = require("webpack");
const path = require("path");
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CopyWebpackPlugin = require('copy-webpack-plugin');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin")

const assetsPath = path.resolve(__dirname, "resources/assets");

module.exports = {
    entry: {
        "planning": assetsPath + "/javascripts/entry-planning.js",
        "semesterstatus": assetsPath + "/javascripts/entry-semesterstatus.js",
        "timeline": assetsPath + "/javascripts/entry-timeline.js",
        "statisticswidget": assetsPath + "/javascripts/entry-statistics.js",
        "planningrequest": assetsPath + "/javascripts/entry-planningrequest.js"
    },
    output: {
        path: path.resolve(__dirname, "assets"),
        chunkFilename: "javascripts/[name].chunk.js",
        filename: "javascripts/[name].js"
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: "css-loader",
                        options: {
                            url: false,
                            importLoaders: 1
                        }
                    },
                    {
                        loader: "postcss-loader"
                    }
                ]
            },
            {
                test: /\.scss$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.(ttf|woff|eot|svg)(\?.+)?$/,
                use: {
                    loader: 'url-loader',
                    options: {
                        limit: 8192
                    }
                }
            },
        ]
    },
    plugins: [
        new VueLoaderPlugin(),
        new MiniCssExtractPlugin({
            filename: "stylesheets/[name].css",
            chunkFilename: "stylesheets/[id].css"
        }),
        new CopyWebpackPlugin([
            {
                from: './node_modules/jquery.timeline.psk/dist/fonts',
                to: 'stylesheets/fonts'
            },
            {
                from: './node_modules/jquery.timeline.psk/dist/langs',
                to: 'stylesheets/langs'
            }
        ])
    ],
    externals: {
        "jquery": "jQuery"
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
            'jsassets': path.resolve(__dirname, 'resources/assets/javascripts/')
        },
        extensions: ['*', '.js', '.vue', '.json']
    },
    optimization: {
        minimizer: [
            new OptimizeCSSAssetsPlugin({
                cssProcessorOptions: {
                    discardComments: {
                        removeAll: true
                    }
                }
            })
        ]
    }
};
