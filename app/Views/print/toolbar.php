<?php
// Print Toolbar (No Print)
// Expected variables: $templates (array), $currentTemplate (id or 'default'), $session (string)
// Also preserves current query params (like ids=...)

$currentQuery = $_GET;
unset($currentQuery['template']); // Remove old template param
$queryString = http_build_query($currentQuery);
$baseUrl = strtok($_SERVER['REQUEST_URI'], '?');

// If ids is missing (e.g. Quick Print single ID in segment), we don't need to append it if it's not in GET.
// Logic: Reload current URL but with new template param.
?>
<div class="no-print" style="position: sticky; top:0; background: #fff; border-bottom: 1px solid #ddd; padding: 10px; z-index: 50; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <div style="display: flex; align-items: center; gap: 10px;">
        <label style="font-size: 14px; font-weight: bold; color: #333;">Template:</label>
        <select onchange="changeTemplate(this.value)" style="padding: 5px; border: 1px solid #ccc; rounded: 4px;">
            <option value="default" <?= $currentTemplate === 'default' ? 'selected' : '' ?>>Default Thermal</option>
            <?php if (! empty($templates)) { ?>
                <?php foreach ($templates as $t) { ?>
                    <option value="<?= $t['id'] ?>" <?= (string) $currentTemplate === (string) $t['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['name']) ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>
    </div>
    
    <div>
        <button onclick="window.print()" style="padding: 6px 16px; background: #0070f3; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Print</button>
        <button onclick="window.close()" style="padding: 6px 16px; background: #eee; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; margin-left: 5px;">Close</button>
    </div>
</div>

<script>
    function changeTemplate(val) {
        const currentUrl = new URL(window.location.href);
        if (val === 'default') {
            currentUrl.searchParams.delete('template');
        } else {
            currentUrl.searchParams.set('template', val);
        }
        window.location.href = currentUrl.toString();
    }
</script>
