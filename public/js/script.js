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

// This function prepares the webpage to respond to user actions.
// It connects buttons and form fields to actions - like when you click a navigation tab,
// type in the search box, or click buttons to filter or sort scholarships.
// It also sets up what happens when you interact with popups.
// No parameters.
// No return value.
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

// This function gets all the scholarship information from the website's server.
// It asks the server for the complete list of scholarships, saves that information,
// and then shows all the scholarships on the webpage as cards you can browse.
// No parameters.
// No return value.
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

// This function gets the list of education levels and fields of study from the server.
// It fills in the dropdown menus where users can choose to filter scholarships
// by things like "Bachelor's degree" or "Engineering field".
// No parameters.
// No return value.
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

// This function changes which part of the webpage is showing when you click navigation buttons.
// For example, clicking "Scholarships" shows the scholarship list, clicking "Bookmarks" shows saved scholarships.
// It highlights the button you clicked and smoothly transitions to the new section.
// Parameters:
//   sectionId: string - The name of the page section to show
// No return value.
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

// This function narrows down the scholarship list based on what you're looking for.
// You can search by typing words, choose education level, pick a field of study, or set a deadline.
// It asks the server for scholarships that match your choices and shows only those.
// No parameters.
// No return value.
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

// This function rearranges scholarships so the ones with earliest deadlines appear first.
// It's like organizing your homework by due date, with urgent ones at the top.
// It gets the sorted list from the server and shows it on the page.
// No parameters.
// No return value.
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

// This function rearranges scholarships from highest money amount to lowest.
// It's like sorting prizes from most valuable to least valuable.
// It gets the sorted list from the server and shows it on the page.
// No parameters.
// No return value.
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

// This function clears all your search and filter choices and shows the complete scholarship list again.
// It's like starting fresh - it empties the search box and resets all dropdown menus,
// then loads and displays every scholarship available.
// No parameters.
// No return value.
function resetFilters() {
    searchInput.value = '';
    levelFilter.value = '';
    fieldFilter.value = '';
    deadlineFilter.value = '';
    // Reload original data
    loadScholarships();
}

// This function creates the visual cards that show scholarship information on the webpage.
// For each scholarship, it makes a nice-looking card with the details and adds it to the page.
// If there are no scholarships to show, it displays a message saying nothing was found.
// Parameters:
//   scholarships: array - The list of scholarships to show as cards
// No return value.
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

// This function builds one scholarship card that you see on the webpage.
// It takes all the scholarship information and formats it nicely - showing the money amount,
// deadline date, provider, and buttons to view details or save as bookmark.
// It also checks if you've already bookmarked this scholarship.
// Parameters:
//   scholarship: object - All the information about one scholarship
// Returns: HTMLElement - A ready-to-display card for that scholarship
function createScholarshipCard(scholarship) {
    const card = document.createElement('div');
    card.className = 'scholarship-card'; // Matches CSS class
    
    const deadline = new Date(scholarship.deadline);
    const formattedDeadline = deadline.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    
    // Format currency
    const formattedAmount = parseFloat(scholarship.amount).toLocaleString('en-PH', {
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
    
    // Check bookmark status visually
    checkIfBookmarked(scholarship.id, card.querySelector('.btn-bookmark'));
    
    return card;
}

// This function opens a popup window with full details about a specific scholarship.
// When you click "View Details" on a scholarship card, this finds that scholarship's information,
// formats it nicely with all the details like eligibility requirements and application link,
// and shows it in a popup window.
// Parameters:
//   scholarshipId: int - The unique number identifying which scholarship to show
// No return value.
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

// This function closes the popup window that shows scholarship details.
// It makes the popup disappear when you click the X button or outside the popup.
// No parameters.
// No return value.
function closeDetailModal() {
    modal.classList.remove('show');
}

// This function saves or unsaves a scholarship to your personal bookmarks list.
// If you haven't bookmarked it yet, it adds it to your saved list.
// If it's already bookmarked, it removes it from your saved list.
// Parameters:
//   scholarshipId: int - The unique number of the scholarship
//   button: HTMLElement - The bookmark button that was clicked
// No return value.
function toggleBookmark(scholarshipId, button) {
    if (button.classList.contains('bookmarked')) {
        removeBookmark(scholarshipId, button);
    } else {
        addBookmark(scholarshipId, button);
    }
}

// This function saves a scholarship to your personal bookmarks.
// It tells the server to remember that you want to keep this scholarship in your saved list.
// The bookmark button changes to show it's now saved.
// Parameters:
//   scholarshipId: int - The unique number of the scholarship to save
//   button: HTMLElement - The bookmark button that shows the save status
// No return value.
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
        } else {
            alert(data.message || 'Error adding bookmark');
        }
    })
    .catch(error => console.error('Error:', error));
}

// This function removes a scholarship from your personal bookmarks.
// It tells the server to forget that you saved this scholarship.
// The bookmark button changes back to show it's not saved anymore.
// If you're looking at your bookmarks page, it refreshes the list.
// Parameters:
//   scholarshipId: int - The unique number of the scholarship to unsave
//   button: HTMLElement - The bookmark button that shows the save status
// No return value.
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

// This function checks if you've already saved a scholarship to your bookmarks.
// It asks the server if this scholarship is in your saved list,
// and updates the bookmark button to show the correct icon (saved or not saved).
// Parameters:
//   scholarshipId: int - The unique number of the scholarship to check
//   button: HTMLElement - The bookmark button to update
// No return value.
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

// This function gets your saved scholarships and shows them on the bookmarks page.
// It asks the server for all the scholarships you've bookmarked,
// then displays them as cards. If you haven't saved any, it shows a message.
// If you're not logged in, it reminds you to sign in to see bookmarks.
// No parameters.
// No return value.
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

// This function makes text safe to display on a webpage.
// It converts special characters that could be used for hacking (like <script> tags)
// into safe versions that just show as text. This prevents security problems.
// Parameters:
//   text: string - The text that might have unsafe characters
// Returns: string - The same text but with dangerous characters made safe
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