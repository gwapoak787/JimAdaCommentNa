// Main JavaScript file for Scholarship Finder

// DOM elements
const navBtns = document.querySelectorAll('.nav-btn');
const contentSections = document.querySelectorAll('.content-section');
const searchSection = document.querySelector('.search-section'); // Added selector for visual toggling
const searchInput = document.getElementById('searchInput');
const levelFilter = document.getElementById('levelFilter');
const fieldFilter = document.getElementById('fieldFilter');
const deadlineFilter = document.getElementById('deadlineFilter');
const sortDeadlineBtn = document.getElementById('sortDeadline');
const sortAmountBtn = document.getElementById('sortAmount');
const resetBtn = document.getElementById('resetBtn');
const scholarshipsList = document.getElementById('scholarshipsList');
const bookmarksList = document.getElementById('bookmarksList');
const modal = document.getElementById('detailModal');
const closeModal = document.querySelector('.close');

// All scholarships data storage
let allScholarships = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadScholarships();
    loadFilters();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Navigation Tabs
    navBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            switchSection(this.dataset.section);
        });
    });
    
    // Filters and search inputs
    if(searchInput) searchInput.addEventListener('keyup', filterScholarships);
    if(levelFilter) levelFilter.addEventListener('change', filterScholarships);
    if(fieldFilter) fieldFilter.addEventListener('change', filterScholarships);
    if(deadlineFilter) deadlineFilter.addEventListener('change', filterScholarships);
    
    // Sort buttons
    if(sortDeadlineBtn) sortDeadlineBtn.addEventListener('click', sortByDeadline);
    if(sortAmountBtn) sortAmountBtn.addEventListener('click', sortByAmount);
    if(resetBtn) resetBtn.addEventListener('click', resetFilters);
    
    // Modal Interactions
    if(closeModal) closeModal.addEventListener('click', closeDetailModal);
    if(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeDetailModal();
        });
    }
}

// Load all scholarships from API
function loadScholarships() {
    fetch('includes/api.php?action=get_all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allScholarships = data.data;
                displayScholarships(allScholarships);
            }
        })
        .catch(error => console.error('Error loading scholarships:', error));
}

// Load filter options dynamically
function loadFilters() {
    fetch('includes/api.php?action=get_filters')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate levels dropdown
                if(levelFilter) {
                    data.levels.forEach(level => {
                        const option = document.createElement('option');
                        option.value = level;
                        option.textContent = level;
                        levelFilter.appendChild(option);
                    });
                }
                
                // Populate fields dropdown
                if(fieldFilter) {
                    data.fields.forEach(field => {
                        const option = document.createElement('option');
                        option.value = field;
                        option.textContent = field;
                        fieldFilter.appendChild(option);
                    });
                }
            }
        })
        .catch(error => console.error('Error loading filters:', error));
}

// Switch content section (Tab switching)
function switchSection(sectionId) {
    // Update nav buttons visual state
    navBtns.forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`.nav-btn[data-section="${sectionId}"]`);
    if(activeBtn) activeBtn.classList.add('active');
    
    // Update content sections visibility
    contentSections.forEach(section => section.classList.remove('active'));
    const activeSection = document.getElementById(sectionId);
    if(activeSection) {
        activeSection.classList.add('active');
        // Animation trigger
        activeSection.style.animation = 'none';
        activeSection.offsetHeight; /* trigger reflow */
        activeSection.style.animation = 'fadeIn 0.5s ease-out';
    }

    // Toggle Search Section Visibility (Only show on 'scholarships' tab)
    if(searchSection) {
        if(sectionId === 'scholarships') {
            searchSection.style.display = 'block';
        } else {
            searchSection.style.display = 'none';
        }
    }
    
    // Load bookmarks specifically if that tab is chosen
    if (sectionId === 'bookmarks') {
        loadBookmarks();
    }
}

// Filter logic
function filterScholarships() {
    const searchTerm = searchInput.value.toLowerCase();
    const level = levelFilter.value;
    const field = fieldFilter.value;
    const deadline = deadlineFilter.value;
    
    // Construct API query
    let params = new URLSearchParams();
    if (level) params.append('level', level);
    if (field) params.append('field', field);
    if (deadline) params.append('deadline', deadline);
    
    let url = 'includes/api.php?action=search';
    if (params.toString()) {
        url += '&' + params.toString();
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let filtered = data.data;
                
                // Client-side text search (title or provider)
                if (searchTerm) {
                    filtered = filtered.filter(s => 
                        s.title.toLowerCase().includes(searchTerm) || 
                        s.provider.toLowerCase().includes(searchTerm)
                    );
                }
                
                displayScholarships(filtered);
            }
        })
        .catch(error => console.error('Error filtering:', error));
}

// Sorting logic
function sortByDeadline() {
    fetch('includes/api.php?action=sort_deadline')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allScholarships = data.data;
                displayScholarships(allScholarships);
            }
        });
}

function sortByAmount() {
    fetch('includes/api.php?action=sort_amount')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allScholarships = data.data;
                displayScholarships(allScholarships);
            }
        });
}

function resetFilters() {
    searchInput.value = '';
    levelFilter.value = '';
    fieldFilter.value = '';
    deadlineFilter.value = '';
    // Reload original data
    loadScholarships();
}

// Display function (Generates the Cards)
function displayScholarships(scholarships) {
    if (scholarships.length === 0) {
        scholarshipsList.innerHTML = `
            <div class="empty-state">
                <p>No scholarships found matching your criteria.</p>
            </div>`;
        return;
    }
    
    scholarshipsList.innerHTML = '';
    
    scholarships.forEach(scholarship => {
        const card = createScholarshipCard(scholarship);
        scholarshipsList.appendChild(card);
    });
}

// Create HTML for a single card
function createScholarshipCard(scholarship) { 
    const card = document.createElement('div'); // creates individual scholarship cards using document.createElement
    card.className = 'scholarship-card'; 
    
    const deadline = new Date(scholarship.deadline);
    const formattedDeadline = deadline.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    
    // Format currency
    const formattedAmount = parseFloat(scholarship.amount).toLocaleString('en-PH', {
        //parsFloat to ensure it's a number
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0
    });
    
    card.innerHTML = `
        <h3>${escapeHtml(scholarship.title)}</h3>
        <div class="scholarship-info">
            <p>Provider: <span>${escapeHtml(scholarship.provider)}</span></p>
            <p>Level: <span>${escapeHtml(scholarship.education_level)}</span></p>
            <p>Field: <span>${escapeHtml(scholarship.field)}</span></p>
        </div>
        
        <div class="scholarship-amount">${formattedAmount}</div>
        <div class="scholarship-deadline">📅 Deadline: ${formattedDeadline}</div>
        
        
        <div class="scholarship-actions">
            <button class="btn" onclick="showDetails(${scholarship.id})">View Details</button>
            <button class="btn btn-bookmark" onclick="toggleBookmark(${scholarship.id}, this)">Bookmark</button>
        </div>
    `;
    //This line is part of the createScholarshipCard() function and creates a button that, when clicked, 
    //calls the showDetails(scholarshipId) function (defined at line 262) with the specific scholarship's ID as a parameter.
    //The showDetails() function then retrieves the scholarship data, formats it, and displays it in a modal popup.
    
    // Check bookmark status visually
    checkIfBookmarked(scholarship.id, card.querySelector('.btn-bookmark'));
    
    return card;
}

// Show Modal with Details
// when "View Details" is clicked on a scholarship card 
// to show more information about the scholarship
// including eligibility criteria and application link.

function showDetails(scholarshipId) {
    const scholarship = allScholarships.find(s => s.id == scholarshipId);
    if (!scholarship) return;
    
    const deadline = new Date(scholarship.deadline);
    const formattedDeadline = deadline.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    const formattedAmount = parseFloat(scholarship.amount).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
    
    const modalBody = document.getElementById('modalBody');
    
    // Populating Modal Content
    modalBody.innerHTML = `
        <h2 style="color: var(--accent); margin-bottom: 20px;">${escapeHtml(scholarship.title)}</h2>
        
        <div style="background: var(--accent-light); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
            <div class="scholarship-info">
                <p><strong>Provider:</strong> ${escapeHtml(scholarship.provider)}</p>
                <p><strong>Education Level:</strong> ${escapeHtml(scholarship.education_level)}</p>
                <p><strong>Field:</strong> ${escapeHtml(scholarship.field)}</p>
                <p><strong>Type:</strong> ${escapeHtml(scholarship.scholarship_type)}</p>
                <p><strong>Amount:</strong> <span style="color: var(--accent); font-weight:bold;">${formattedAmount}</span></p>
                <p><strong>Deadline:</strong> <span style="color: #dc2626;">${formattedDeadline}</span></p>
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <h4 style="margin-bottom: 10px;">Eligibility & Description</h4>
            <p style="color: var(--text-muted); line-height: 1.8;">${escapeHtml(scholarship.eligibility)}</p>
        </div>

        <div class="scholarship-actions" style="grid-template-columns: 1fr;">
            <a href="${escapeHtml(scholarship.application_link)}" target="_blank" class="btn">Apply Now</a>
            <button class="btn btn-bookmark" style="width:100%; margin-top:10px;" onclick="toggleBookmark(${scholarship.id}, this)">Bookmark Scholarship</button>
        </div>
    `;
    
    // Update bookmark button state in modal
    checkIfBookmarked(scholarshipId, modalBody.querySelector('.btn-bookmark'));
    
    modal.classList.add('show');
}

function closeDetailModal() {
    modal.classList.remove('show');
}

// Bookmark Logic
function toggleBookmark(scholarshipId, button) {
    if (button.classList.contains('bookmarked')) {
        removeBookmark(scholarshipId, button);
    } else {
        addBookmark(scholarshipId, button);
    }
}

function addBookmark(scholarshipId, button) {
    const formData = new FormData();
    formData.append('scholarship_id', scholarshipId);

    fetch('includes/api.php?action=add_bookmark', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.add('bookmarked');
            button.textContent = 'Bookmarked';

            // Update the corresponding button in the main scholarship grid
            updateMainGridBookmarkStatus(scholarshipId, true);
        } else {
            alert(data.message || 'Error adding bookmark');
        }
    })
    .catch(error => console.error('Error:', error));
}

function removeBookmark(scholarshipId, button) {
    const formData = new FormData();
    formData.append('scholarship_id', scholarshipId);

    fetch('includes/api.php?action=remove_bookmark', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.remove('bookmarked');
            button.textContent = 'Bookmark';

            // Update the corresponding button in the main scholarship grid
            updateMainGridBookmarkStatus(scholarshipId, false);

            // Specific behavior: if we are on the bookmarks tab, reload the list to remove the item instantly
            if (document.getElementById('bookmarks').classList.contains('active')) {
                loadBookmarks();
            }
        } else {
            alert(data.message || 'Error removing bookmark');
        }
    })
    .catch(error => console.error('Error:', error));
}

function checkIfBookmarked(scholarshipId, button) {
    if(!button) return;
    fetch(`includes/api.php?action=is_bookmarked&scholarship_id=${scholarshipId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.is_bookmarked) {
                button.classList.add('bookmarked');
                button.textContent = 'Bookmarked';
            }
        })
        .catch(error => console.error('Error:', error));
}

function loadBookmarks() {
    fetch('includes/api.php?action=get_bookmarks')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data.length === 0) {
                    bookmarksList.innerHTML = '<div class="empty-state"><p>You haven\'t bookmarked any scholarships yet.</p></div>';
                } else {
                    bookmarksList.innerHTML = '';
                    data.data.forEach(scholarship => {
                        const card = createScholarshipCard(scholarship);
                        bookmarksList.appendChild(card);
                    });
                }
            } else {
                bookmarksList.innerHTML = '<div class="empty-state"><p>Please log in to view bookmarks.</p></div>';
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    // Update bookmark status in main scholarship grid
    function updateMainGridBookmarkStatus(scholarshipId, isBookmarked) {
        // Find the scholarship card in the main grid
        const scholarshipCards = document.querySelectorAll('.scholarship-card');
        scholarshipCards.forEach(card => {
            const bookmarkBtn = card.querySelector('.btn-bookmark');
            if (bookmarkBtn && bookmarkBtn.getAttribute('onclick').includes(`toggleBookmark(${scholarshipId}`)) {
                if (isBookmarked) {
                    bookmarkBtn.classList.add('bookmarked');
                    bookmarkBtn.textContent = 'Bookmarked';
                } else {
                    bookmarkBtn.classList.remove('bookmarked');
                    bookmarkBtn.textContent = 'Bookmark';
                }
            }
        });
    }
    
    // Utility: Prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}