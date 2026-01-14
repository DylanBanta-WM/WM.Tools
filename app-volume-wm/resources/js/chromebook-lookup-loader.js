// Import Chromebook Lookup module
import { ChromebookLookup } from './gam/core/chromebook-lookup.js';

// Make globally available
window.ChromebookLookup = ChromebookLookup;

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.chromebookLookup = new ChromebookLookup();
    console.log('Chromebook Lookup initialized');
});

// Export for use in other modules
export {
    ChromebookLookup
};

console.log('Chromebook Lookup module loaded');
