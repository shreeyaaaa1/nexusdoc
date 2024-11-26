<?php
require_once dirname(__FILE__) . '/init.php';
global $conn;

function getReports($user_id) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT id, title, type, created_at, from_date, to_date, content 
        FROM reports 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getReport($report_id, $user_id) {
    global $db;
    $stmt = $db->prepare("
        SELECT id, title, type, created_at, from_date, to_date, content 
        FROM reports 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $report_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function generateReport($data) {
    global $conn;
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Insert report metadata
        $stmt = $conn->prepare("
            INSERT INTO reports (user_id, title, type, from_date, to_date, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("issss", 
            $data['user_id'],
            $data['title'],
            $data['type'],
            $data['from_date'],
            $data['to_date']
        );
        $stmt->execute();
        $report_id = $db->insert_id;
        
        // Generate report content based on type
        $content = generateReportContent($data['type'], $data['user_id'], $data['from_date'], $data['to_date']);
        
        // Update report with content
        $stmt = $db->prepare("UPDATE reports SET content = ? WHERE id = ?");
        $stmt->bind_param("si", $content, $report_id);
        $stmt->execute();
        
        $db->commit();
        return $report_id;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

function generateReportContent($type, $user_id, $from_date, $to_date) {
    global $db;
    $content = '';
    
    switch ($type) {
        case 'research_summary':
            // Get research projects created in date range
            $stmt = $db->prepare("
                SELECT title, description, created_at 
                FROM projects 
                WHERE user_id = ? AND created_at BETWEEN ? AND ?
            ");
            $stmt->bind_param("iss", $user_id, $from_date, $to_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $projects = $result->fetch_all(MYSQLI_ASSOC);
            
            $content = "<h2>Research Summary</h2>";
            $content .= "<p>Total projects in period: " . count($projects) . "</p>";
            foreach ($projects as $project) {
                $content .= "<div class='project-summary'>";
                $content .= "<h3>" . htmlspecialchars($project['title']) . "</h3>";
                $content .= "<p>" . htmlspecialchars($project['description']) . "</p>";
                $content .= "<p>Created: " . date('M j, Y', strtotime($project['created_at'])) . "</p>";
                $content .= "</div>";
            }
            break;
            
        case 'project_progress':
            // Get project updates in date range
            $stmt = $db->prepare("
                SELECT p.title as project_title, u.content, u.created_at 
                FROM project_updates u 
                JOIN projects p ON u.project_id = p.id 
                WHERE p.user_id = ? AND u.created_at BETWEEN ? AND ?
                ORDER BY u.created_at ASC
            ");
            $stmt->bind_param("iss", $user_id, $from_date, $to_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $updates = $result->fetch_all(MYSQLI_ASSOC);
            
            $content = "<h2>Project Progress Report</h2>";
            $content .= "<p>Total updates in period: " . count($updates) . "</p>";
            foreach ($updates as $update) {
                $content .= "<div class='progress-update'>";
                $content .= "<h3>" . htmlspecialchars($update['project_title']) . "</h3>";
                $content .= "<p>" . htmlspecialchars($update['content']) . "</p>";
                $content .= "<p>Updated: " . date('M j, Y', strtotime($update['created_at'])) . "</p>";
                $content .= "</div>";
            }
            break;
            
        case 'activity_log':
            // Get user activity in date range
            $stmt = $db->prepare("
                SELECT activity_type, description, created_at 
                FROM activity_log 
                WHERE user_id = ? AND created_at BETWEEN ? AND ?
                ORDER BY created_at DESC
            ");
            $stmt->bind_param("iss", $user_id, $from_date, $to_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $activities = $result->fetch_all(MYSQLI_ASSOC);
            
            $content = "<h2>Activity Log</h2>";
            $content .= "<p>Total activities in period: " . count($activities) . "</p>";
            foreach ($activities as $activity) {
                $content .= "<div class='activity'>";
                $content .= "<strong>" . htmlspecialchars($activity['activity_type']) . "</strong>";
                $content .= "<p>" . htmlspecialchars($activity['description']) . "</p>";
                $content .= "<p>Time: " . date('M j, Y g:i A', strtotime($activity['created_at'])) . "</p>";
                $content .= "</div>";
            }
            break;
    }
    
    return $content;
}