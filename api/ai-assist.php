<?php
// api/ai-assist.php
// Called by JS fetch from the editor page.
header('Content-Type: application/json');
require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../config/claude.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true);
$action  = $body['action']  ?? '';
$content = $body['content'] ?? '';
$title   = $body['title']   ?? '';
$pageId  = (int)($body['page_id'] ?? 0);

// ── Build prompt based on action ──────────────────────────
$prompts = [
    'improve'  => "You are an expert content editor. Improve the writing quality, clarity, and flow of the following content. Keep the same meaning and length. Return only the improved content, nothing else.",
    'rewrite'  => "You are an expert copywriter. Completely rewrite the following content in a more engaging, professional tone. Keep the same meaning. Return only the rewritten content, nothing else.",
    'shorten'  => "You are an expert editor. Shorten the following content to about half its length while keeping the key points. Return only the shortened content, nothing else.",
    'seo'      => "You are an SEO expert. Rewrite the following content to be more SEO-friendly: use relevant keywords naturally, improve headings, and make it scannable. Return only the improved content, nothing else.",
    'generate' => "You are a professional web content writer. Write a complete, well-structured page content for a page titled: \"{$title}\". Make it professional, engaging, and about 200-300 words. Return only the content, nothing else.",
];

$systemPrompt = $prompts[$action] ?? $prompts['improve'];
$userMessage  = ($action === 'generate') ? "Page title: {$title}" : $content;

if (empty(trim($userMessage))) {
    echo json_encode(['success' => false, 'error' => 'No content provided.']);
    exit;
}

// ── Call Claude ────────────────────────────────────────────
$result = callClaude($systemPrompt, $userMessage);

// ── Log to database ───────────────────────────────────────
if ($pageId > 0) {
    logAIRequest($pageId, $action, $userMessage, $result['content'] ?? '');
}

echo json_encode($result);
