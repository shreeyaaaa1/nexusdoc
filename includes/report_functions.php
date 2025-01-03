<?php
require_once dirname(__FILE__) . '/init.php';

function getReports($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getReport($report_id, $user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM reports WHERE id = ? AND user_id = ?");
    $stmt->bindParam(1, $report_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function generateReport($data) {
    global $conn;
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Insert report metadata
        $stmt = $conn->prepare("
            INSERT INTO reports (user_id, title, description, type, from_date, to_date, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bindParam(1, $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $data['title'], PDO::PARAM_STR);
        $stmt->bindParam(3, $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(4, $data['type'], PDO::PARAM_STR);
        $stmt->bindParam(5, $data['from_date'], PDO::PARAM_STR);
        $stmt->bindParam(6, $data['to_date'], PDO::PARAM_STR);
        $stmt->execute();
        $report_id = $conn->lastInsertId();
        
        // Generate report content based on type
        $content = generateReportContent(
            $data['type'],
            $data['user_id'],
            $data['from_date'],
            $data['to_date']
        );
        
        // Update report with content
        $stmt = $conn->prepare("UPDATE reports SET content = ? WHERE id = ?");
        $stmt->bindParam(1, $content, PDO::PARAM_STR);
        $stmt->bindParam(2, $report_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $conn->commit();
        return $report_id;
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

function generateReportContent($type, $user_id, $from_date, $to_date) {
    // Implementation of report content generation based on type
    // This would be customized based on requirements
    return "Report content for $type from $from_date to $to_date";
}