import os

workspace = r"c:\Users\HP\Documents\Jews-world Backend\picme225.com_backend"
filepath = os.path.join(workspace, "app", "Http", "Controllers", "SecureChatController.php")

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Normalisation des sauts de ligne pour recherche
content_norm = content.replace('\r\n', '\n').replace('\r', '\n')

target = """        $msg->is_flagged     = $isFlagged;
        $msg->is_blocked     = $isBlocked;
        $msg->lead_score     = $leadScore;
        $msg->ai_used        = $regexAnalysis['ai_used'] ?? false;
        $msg->save();"""

replacement = """        $msg->is_flagged     = $isFlagged;
        $msg->is_blocked     = $isBlocked;
        $msg->lead_score     = $leadScore;
        $msg->ai_used        = $regexAnalysis['ai_used'] ?? false;
        $msg->save();

        // Dispatch background AI moderation if not blocked by synchronous Regex shield
        if (!$isBlocked) {
            try {
                \\App\\Jobs\\ModerateChatMessageJob::dispatch(
                    $msg->id,
                    $rawMessage,
                    $userId,
                    $recipientId,
                    $request->listing_id ?? $request->announcement_id,
                    $sender->cancellation_strikes ?? 0
                );
            } catch (\\Exception $e) {
                \\Log::error("Failed to dispatch ModerateChatMessageJob: " . $e->getMessage());
            }
        }"""

if target in content_norm:
    content_norm = content_norm.replace(target, replacement)
    content = content_norm.replace('\n', os.linesep)
    with open(filepath, 'w', encoding='utf-8', newline='') as f:
        f.write(content)
    print("SUCCESS: SecureChatController.php mis à jour !")
else:
    print("ERROR: Cible non trouvée dans SecureChatController.php")
