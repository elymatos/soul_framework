import './bootstrap';
import Alpine from 'alpinejs';

import Chart from 'chart.js/auto';

import svgPanZoom from "svg-pan-zoom";
import ky from 'ky';
import Split from 'split.js';

import './components/messengerComponent.js';
import browseSearchComponent from './components/browseSearchComponent.js';
import searchComponent from './components/searchComponent.js';
import treeComponent from './components/treeComponent.js';

import '../css/app.less';

window.Chart = Chart;
window.svgPanZoom = svgPanZoom;
window.ky = ky;
window.Split = Split;

document.addEventListener("DOMContentLoaded", () => {
    Alpine.data('searchComponent', searchComponent);
    Alpine.data('treeComponent', treeComponent);
    Alpine.data('browseSearchComponent', browseSearchComponent);
    Alpine.start();
});

