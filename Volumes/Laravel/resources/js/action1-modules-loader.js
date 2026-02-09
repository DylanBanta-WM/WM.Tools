// Action1 API Module Loader
import { Action1Api } from './action1/core/action1-api.js';

// Make globally available
window.Action1Api = Action1Api;

// Initialize global instance
window.action1Api = Action1Api.getInstance();

// Export for use in other modules
export { Action1Api };

console.log('Action1 modules loaded and made globally available');
