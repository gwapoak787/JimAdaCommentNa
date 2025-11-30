<?php
// Main dashboard page
include 'includes/config.php';
include 'includes/auth.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Finder</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>"> 
    </head>
<body>

    <div class="container">
        <header class="header">
            <div class="header-content">
                <div class="brand-section">
                    <div class="logo-title">
                        <!-- The logo is displayed using an img tag -->
                        <img src="451e2ae8-5fb5-47db-8508-9ef6a65a2dd8.jpeg" alt="Scholarship Finder Logo" class="logo">
                        <div>
                            <h1>Scholarship Finder</h1>
                            <p class="tagline">Discover Your Path to Educational Excellence</p>
                        </div>
                    </div>
                </div>
                <div class="user-info">
                    <span>Welcome, <span style="color: var(--accent);"><?php echo htmlspecialchars($username); ?></span></span>
                    <a href="logout.php" class="btn-logout">Logout</a>
                    <!-- logout button if the user wants to end their session -->
                </div>
            </div>
        </header>

         <!-- Navigation uses <nav> with <button> elements, each with data-section attributes-->
        <nav class="nav">
            <button class="nav-btn active" data-section="scholarships">
                <span class="nav-icon">📚</span> Scholarships
            </button>
            <button class="nav-btn" data-section="bookmarks">
                <span class="nav-icon">🔖</span> My Bookmarks
            </button>
            <button class="nav-btn" data-section="about">
                <span class="nav-icon">ℹ️</span> About
            </button>
            <?php if ($userId == 1): ?>
            <a href="admin.php" class="nav-btn admin-btn">
                <span class="nav-icon">⚙️</span> Admin
            <!-- Admin link visible only to admin user (user_id = 1) -->
            </a>
            <?php endif; ?>
        </nav>
        <!-- emoji icons are wrap in <span> elements with class "nav-icon"-->


        <section id="searchSection" class="search-section">
            <h2>Find Scholarships</h2>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search by title or provider...">
                <!--allows users to search scholarships by title or provider name with real-time filtering-->
                <select id="levelFilter">
                    <option value="">All Levels</option>
                    </select>
                <!-- the level filter uses dropdown to select educational level -->
                <select id="fieldFilter">
                    <option value="">All Fields</option>
                    </select>
                <!-- the same with the level filter, the field filter uses dropdown to select field of study -->
                <input type="date" id="deadlineFilter" placeholder="Deadline">
                <!-- the deadline filter uses date picker to select deadline -->

                <div class="sort-buttons">
                    <button id="sortDeadline" class="sort-btn">Sort by Deadline</button>
                    <button id="sortAmount" class="sort-btn">Sort by Amount</button>
                </div>
                <!-- the sort buttons are used to sort the scholarships by deadline or amount -->

                <button id="resetBtn" class="btn-reset">Reset</button>
                <!-- the use of the reset button is to clear all filters and sorting -->
            </div>
        </section>

        <div class="section-separator"></div>

        <!-- Main content area with sections for scholarships, bookmarks, and about -->
        <!-- the section is used to display different content based on navigation selection -->
        <main class="main-content">
            <section id="scholarships" class="content-section active">
                <div id="scholarshipsList" class="scholarships-grid">
                    </div>
            </section>

            <section id="bookmarks" class="content-section">
                <div id="bookmarksList" class="scholarships-grid">
                    </div>
            </section>

            <section id="about" class="content-section">
                <div class="about-content">
                    <div class="about-section">
                        <h3 data-icon="🎯">Our Mission</h3>
                        <p>"To empower every student by providing a seamless, transparent, and comprehensive platform that connects them directly to the financial aid they need to pursue educational excellence."</p>
                    </div>

                    <div class="about-section features-section">
                        <h3 data-icon="⚙️">What We Do</h3>
                        <div class="features-list">
                            <div class="feature-item">
                                <h4 data-icon="🔍">Scholarship Discovery</h4>
                                <p>Find relevant scholarships through our comprehensive database.</p>
                            </div>
                            <div class="feature-item">
                                <h4 data-icon="📝">Easy Application</h4>
                                <p>Streamlined application process with bookmarking features.</p>
                            </div>
                        </div>
                    </div>

                    <div class="about-section team-section">
                        <h3 data-icon="👥">Our Team</h3>
                        <div class="team-members">
                            <div class="team-member"><h4>Alcaraz, Veinson</h4><p>Leader</p></div>
                            <div class="team-member"><h4>Banasen, Rogelio</h4><p>Backend</p></div>
                            <div class="team-member"><h4>Bandaw, Kenlly</h4><p>Frontend</p></div>
                            <div class="team-member"><h4>Bantowag, Jim Frans</h4><p>Frontend</p></div>
                            <div class="team-member"><h4>Gadit, Kenneth</h4><p>Frontend</p></div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalBody"></div>
        </div>
    </div>

    <script src="js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>