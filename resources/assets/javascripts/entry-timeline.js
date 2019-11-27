import './public-path'
import WhakamahereTimeline from './lib/timeline'
import 'jquery.timeline.psk/dist/timeline.min.css';
import '../stylesheets/timeline.scss'

$(function() {
    WhakamahereTimeline.init();
});
