const webpack = require("webpack");
const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CopyWebpackPlugin = require('copy-webpack-plugin');

const assetsPath = path.resolve(__dirname, "resources");

module.exports = {
    entry: {
        "planning": assetsPath + "/javascripts/entry-planning.js",
        "semesterstatus": assetsPath + "/javascripts/entry-semesterstatus.js",
        "timeline": assetsPath + "/javascripts/entry-timeline.js",
        "timeline-style": assetsPath + "/stylesheets/timeline.scss"
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
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: "css-loader",
                        options: {
                            url: false,
                            importLoaders: 2
                        }
                    },
                    {
                        loader: "postcss-loader"
                    },
                    {
                        loader: "sass-loader"
                    }
                ]
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: "stylesheets/[name].css",
            chunkFilename: "stylesheets/[id].css"
        }),
        new CopyWebpackPlugin([
            {
                from: './node_modules/jquery.timeline.psk/dist/fonts',
                to: 'timeline/fonts'
            },
            {
                from: './node_modules/jquery.timeline.psk/dist/langs',
                to: 'timeline/langs'
            }
        ])
    ],
    externals: {
        "jquery": "jQuery"
    },
    resolve: {
        alias: {
            fullcalendar: '@fullcalendar/dist/fullcalendar',
            scheduler: '@fullcalendar-scheduler/dist/scheduler',
            timeline: '@fullcalendar/resource-timeline/main'
        }
    }
};
