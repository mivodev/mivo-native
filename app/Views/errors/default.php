<?php
// Default values if not provided
$errorCode = isset($code) ? $code : 404;
$errorMessage = isset($message) ? $message : 'Page Not Found';
$errorDescription = isset($description) ? $description : 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.';

// Ensure title is set for header.php
$title = "$errorCode - $errorMessage";
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<div class="flex-grow flex flex-col items-center justify-center w-full">
    <div class="text-center px-4">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-accents-2 mb-8">
            <i data-lucide="alert-triangle" class="w-10 h-10 text-accents-5"></i>
        </div>
        
        <h1 class="text-6xl font-extrabold tracking-tighter mb-4 text-foreground"><?= $errorCode ?></h1>
        
        <!-- Use data-i18n if message looks like a key (starts with errors.), otherwise show raw -->
        <h2 class="text-2xl font-bold mb-4 text-foreground" <?= (strpos($errorMessage, 'errors.') === 0) ? 'data-i18n="'.$errorMessage.'"' : '' ?>>
            <?= $errorMessage ?>
        </h2>
        
        <p class="text-accents-5 max-w-md mx-auto mb-8" <?= (strpos($errorDescription, 'errors.') === 0) ? 'data-i18n="'.$errorDescription.'"' : '' ?>>
            <?= $errorDescription ?>
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4 w-full sm:w-auto">
            <a href="/" class="btn btn-primary w-full sm:w-auto" data-i18n="errors.return_home">
                Return Home
            </a>
            <button onclick="history.back()" class="btn btn-secondary w-full sm:w-auto" data-i18n="errors.go_back">
                Go Back
            </button>
        </div>
    </div>
</div>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
