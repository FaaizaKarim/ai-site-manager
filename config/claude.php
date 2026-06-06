<?php
// config/claude.php — Claude API integration

require_once __DIR__ . '/db.php';

function callClaude(string $systemPrompt, string $userMessage): array {
    $apiKey = env('CLAUDE_API_KEY');
    if (empty($apiKey) || str_contains($apiKey, 'paste-your-real-key')) {
        return ['success' => false, 'error' => 'Claude API key not configured. Add your key to .env'];
    }

    $payload = [
        'model'      => env('CLAUDE_MODEL', 'claude-sonnet-4-20250514'),
        'max_tokens' => (int) env('CLAUDE_MAX_TOKENS', '2048'),
        'system'     => $systemPrompt,
        'messages'   => [
            ['role' => 'user', 'content' => $userMessage],
        ],
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT    => 60,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['success' => false, 'error' => 'Network error: ' . $curlError];
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        $errorMsg = $data['error']['message'] ?? "API error (HTTP $httpCode)";
        return ['success' => false, 'error' => $errorMsg];
    }

    $content = '';
    foreach ($data['content'] ?? [] as $block) {
        if ($block['type'] === 'text') {
            $content .= $block['text'];
        }
    }

    return ['success' => true, 'content' => trim($content)];
}

function logAIRequest(int $pageId, string $action, string $userMessage, string $aiResponse): void {
    try {
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO ai_logs (page_id, action, user_message, ai_response) VALUES (?,?,?,?)'
        );
        $stmt->execute([$pageId, $action, $userMessage, $aiResponse]);
    } catch (Exception $e) {
        // Logging failure should not break the AI response
    }
}
