// Import Student Creator
import { StudentCreator } from './gam/core/student-creator.js';

// Make globally available
window.StudentCreator = StudentCreator;

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    window.studentCreator = new StudentCreator();
    console.log('Student Creator initialized');
});

// Export for use in other modules
export {
    StudentCreator
};

console.log('GAM modules loaded and made globally available');
