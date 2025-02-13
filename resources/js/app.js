// resources/js/app.js

// Import styles
import "../css/app.css";

// Import dependencies
import "./bootstrap";
import Alpine from "alpinejs";

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Fetch and load FAQ data
async function loadFaqItems() {
    try {
        const response = await fetch('/data/faq.json');
        const data = await response.json();
        
        const container = document.getElementById('faqContainer');
        if (!container) {
            console.log('FAQ container not found - might be on different page'); // Not an error
            return;
        }
        
        data.faqs.forEach(item => {
            const faqHTML = `
                <div class="faq-item">
                    <input type="checkbox" id="question${item.id}" class="faq-toggle">
                    <label for="question${item.id}" class="faq-question">
                        ${item.question}
                    </label>
                    <div class="faq-answer">
                        ${item.answer}
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', faqHTML);
        });
    } catch (error) {
        console.error('Error loading FAQ:', error);
    }
}

// Make loadFaqItems available globally
window.loadFaqItems = loadFaqItems;

// Call loadFaqItems when document is loaded
document.addEventListener('DOMContentLoaded', loadFaqItems);
