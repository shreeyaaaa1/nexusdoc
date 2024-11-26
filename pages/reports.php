<?php
// Session and auth should already be initialized from index.php
require_once 'includes/report_functions.php';

// Set default action first
$action = $_GET['action'] ?? 'list';

// Debug output to verify page access
error_log('DEBUG: Accessing reports page');
error_log('DEBUG: Session state: ' . (isset($_SESSION['user_id']) ? 'Logged in as user ' . $_SESSION['user_id'] : 'Not logged in'));
error_log('DEBUG: Current page: ' . ($_GET['page'] ?? 'not set'));
error_log('DEBUG: Current action: ' . $action);

// Check authentication first, before any output
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit();
}

switch ($action) {
    case 'list':
        $reports = getReports($_SESSION['user_id']);
        ?>
        <div class="container">
            <h2>Your Reports</h2>
            <div class="mb-4">
                <a href="index.php?page=reports&action=generate" class="btn btn-primary">Generate New Report</a>
            </div>
            <?php if(empty($reports)): ?>
                <p>No reports found. Generate your first report!</p>
            <?php else: ?>
                <div class="reports-list">
                    <?php foreach($reports as $report): ?>
                        <div class="report-item">
                            <h3><?php echo htmlspecialchars($report['title']); ?></h3>
                            <p><?php echo htmlspecialchars($report['description'] ?? ''); ?></p>
                            <a href="index.php?page=reports&action=view&id=<?php echo $report['id']; ?>" class="btn btn-primary">View Report</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        break;
    case 'generate':
        require_once 'pages/reports/generate.php';
        break;
    
    case 'view':
        require_once 'pages/reports/view.php';
        break;
    
    case 'list':
    default:
        require_once 'pages/reports/list.php';
        break;
}