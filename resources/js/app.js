import './bootstrap';

import { initCountdowns } from './countdown';

// 中央初始化：DOM 就绪后扫描所有行为标记并接管
document.addEventListener('DOMContentLoaded', () => {
    initCountdowns();
});