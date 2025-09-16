import './bootstrap';
// import './webcomponents';
import Alpine from 'alpinejs';


import Chart from 'chart.js/auto';

import svgPanZoom from "svg-pan-zoom";
import ky from 'ky';
import Split from 'split.js'

// Component imports
import './components/messengerComponent.js';
import browseSearchComponent from './components/browseSearchComponent.js';
import searchComponent from './components/searchComponent.js';
import treeComponent from './components/treeComponent.js';
import searchFormComponent from './components/searchFormComponent.js';
import dataGridComponent from './components/dataGridComponent.js';

import '../css/fomantic-ui/semantic.less';
// import 'primeflex/primeflex.css';
import '../css/app.less';
// import '../css/webcomponents.scss';

window.Chart = Chart;
window.svgPanZoom = svgPanZoom;
window.ky = ky;
window.Split = Split;


// Make Alpine available globally before any components try to use it
window.Alpine = Alpine;

document.addEventListener("DOMContentLoaded", () => {
    // Register legacy components
    Alpine.data('searchFormComponent', searchFormComponent);
    Alpine.data('searchComponent', searchComponent);
    Alpine.data('treeComponent', treeComponent);
    Alpine.data('browseSearchComponent', browseSearchComponent);
    Alpine.data('dataGrid', dataGridComponent);
    Alpine.start();

});

