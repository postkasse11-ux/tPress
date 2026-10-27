<?php
$configFile = __DIR__ . '/config.php';

/* ------------------------------------------------------------------ *
 * FØRSTEGANGSOPPSETT – med språkvalg
 * ------------------------------------------------------------------ */
if (!file_exists($configFile)) {
    $setupError = '';
    $manualConfig = '';
    $lang = $_POST['site_lang'] ?? 'no';
    
    // Oversettelser for installasjonsskjemaet
    $i18n = [
        'no' => [
            'welcome' => '👋 Velkommen til tPress',
            'intro' => 'Dette ser ut som en ny installasjon. Sett opp nettstedet ditt.',
            'title_label' => 'Nettstedets tittel',
            'lang_label' => 'Språk',
            'pass_label' => 'Admin-passord (minst 8 tegn)',
            'submit' => 'Fullfør oppsett',
            'error_write' => 'Mappen er skrivebeskyttet. Opprett config.php manuelt med innholdet under.',
            'error_pass' => 'Passordet må være på minst 8 tegn.',
            'manual_title' => 'Lag en fil kalt config.php i samme mappe som index.php, og lim inn:',
            'manual_reload' => 'Last deretter siden på nytt.',
        ],
        'en' => [
            'welcome' => '👋 Welcome to tPress',
            'intro' => 'This looks like a fresh installation. Let\'s set up your site.',
            'title_label' => 'Site title',
            'lang_label' => 'Language',
            'pass_label' => 'Admin password (min 8 characters)',
            'submit' => 'Complete setup',
            'error_write' => 'Directory is read-only. Please create config.php manually with the content below.',
            'error_pass' => 'Password must be at least 8 characters.',
            'manual_title' => 'Create a file called config.php in the same directory as index.php, and paste this:',
            'manual_reload' => 'Then reload the page.',
        ]
    ];
    
    $t = $i18n[$lang] ?? $i18n['no'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_password'])) {
        $newTitle = trim($_POST['site_title'] ?? '') ?: 'Min Blogg';
        $newPassword = (string)$_POST['setup_password'];
        $newLang = in_array($_POST['site_lang'] ?? '', ['no', 'en']) ? $_POST['site_lang'] : 'no';

        if (strlen($newPassword) < 8) {
            $setupError = $t['error_pass'];
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);

            $manualConfig = "<?php\n"
                . "// Local configuration – DO NOT commit to GitHub\n"
                . "define('PASSWORD_HASH', " . var_export($hash, true) . ");\n"
                . "define('SITE_TITLE', " . var_export($newTitle, true) . ");\n"
                . "define('SITE_LANG', " . var_export($newLang, true) . ");\n"
                . "define('DEBUG', false);\n";

            if (@file_put_contents($configFile, $manualConfig) !== false) {
                @chmod($configFile, 0640);
                header('Location: index.php');
                exit;
            }

            $setupError = $t['error_write'];
        }
    }
    ?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Setup / Oppsett</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
         max-width: 520px; margin: 4rem auto; padding: 0 1.5rem; line-height: 1.6; color: #1f2328; }
  h1 { font-size: 1.7rem; }
  p { color: #6e7781; }
  label { display: block; font-weight: 600; margin: 1rem 0 .3rem; font-size: .9rem; }
  input, select { width: 100%; padding: .7rem; border: 1px solid #d0d7de; border-radius: 6px;
          font-size: 1rem; box-sizing: border-box; background: #fff; }
  button { margin-top: 1.2rem; background: #0a6c4a; color: #fff; border: none;
           padding: .7rem 1.5rem; border-radius: 6px; font-size: 1rem; cursor: pointer; }
  .error { background: #ffebe9; color: #b00020; padding: .8rem; border-radius: 6px; font-size: .9rem; }
  pre { background: #f6f8fa; padding: 1rem; border-radius: 6px; overflow-x: auto; font-size: .8rem; }
</style>
</head>
<body>
<h1><?= $t['welcome'] ?></h1>
<p><?= $t['intro'] ?></p>
<?php if ($setupError): ?><div class="error"><?= htmlspecialchars($setupError) ?></div><?php endif; ?>
<form method="post">
  <label for="site_title"><?= $t['title_label'] ?></label>
  <input type="text" id="site_title" name="site_title" value="Min Blogg" required>
  
  <label for="site_lang"><?= $t['lang_label'] ?></label>
  <select id="site_lang" name="site_lang" onchange="this.form.submit()">
    <option value="no" <?= $lang === 'no' ? 'selected' : '' ?>>Norsk</option>
    <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
  </select>
  
  <label for="setup_password"><?= $t['pass_label'] ?></label>
  <input type="password" id="setup_password" name="setup_password" required>
  
  <button type="submit"><?= $t['submit'] ?></button>
</form>
<?php if ($manualConfig): ?>
  <p><?= $t['manual_title'] ?></p>
  <pre><?= htmlspecialchars($manualConfig) ?></pre>
  <p><?= $t['manual_reload'] ?></p>
<?php endif; ?>
</body>
</html>
    <?php
    exit;
}

/* ------------------------------------------------------------------ *
 * KONFIGURASJON OG OVERSETTELSER
 * ------------------------------------------------------------------ */
require_once $configFile;

if (!defined('DEBUG')) define('DEBUG', false);
if (!defined('SITE_LANG')) define('SITE_LANG', 'no');

if (DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/php-error.log');
    error_reporting(E_ALL);
}

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
session_start();

define('POSTS_DIR', __DIR__ . '/posts');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('UPLOADS_WEB_PATH', 'uploads/');
define('VIEWS_DIR', __DIR__ . '/views');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
define('MAX_MEDIA_UPLOAD_BYTES', 64 * 1024 * 1024);

/* ------------------------------------------------------------------ *
 * OVERSETTELSER
 * ------------------------------------------------------------------ */
$translations = [
    'no' => [
        'home' => 'Forside',
        'new_post' => 'Nytt innlegg',
        'stats' => 'Statistikk',
        'login' => 'Logg inn',
        'logout' => 'Logg ut',
        'logout_confirm' => 'Logge ut?',
        'search_placeholder' => 'Søk i innlegg og tagger…',
        'search_button' => 'Søk',
        'latest_tags' => 'Siste tagger',
        'showing_tag' => 'Viser taggen',
        'show_all' => 'Vis alle',
        'search_results' => 'Søk: «%s» · %d treff ·',
        'no_posts' => 'Ingen innlegg å vise.',
        'read_more' => 'Les mer',
        'views' => 'visninger',
        'edit' => '✏️ Rediger',
        'delete' => '🗑 Slett',
        'delete_confirm' => 'Slette innlegget?',
        'back' => '← Tilbake',
        'back_to_home' => '← Tilbake til forsiden',
        'edit_post' => 'Rediger innlegg',
        'new_post_title' => 'Nytt innlegg',
        'upload_media' => 'Last opp bilde, lyd eller video',
        'upload_button' => 'Last opp',
        'select_file' => 'Velg en fil først.',
        'uploading' => 'Laster opp…',
        'upload_success' => 'Opplastet og satt inn i teksten.',
        'upload_error' => 'Opplasting feilet.',
        'network_error' => 'Nettverksfeil.',
        'media_audio_video' => 'Lyd og video (klikk for å sette inn):',
        'previous_images' => 'Tidligere bilder:',
        'title_label' => 'Tittel',
        'tags_label' => 'Tagger (kommaseparert)',
        'tags_hint' => 'Skill flere tagger med komma. Tagger vises på forsiden og brukes som kategorier.',
        'edit_tab' => 'Rediger tekst',
        'preview_tab' => '👁 Forhåndsvisning',
        'loading' => 'Laster…',
        'empty_preview' => 'Tomt.',
        'preview_error' => 'Feil under forhåndsvisning.',
        'markdown_hint' => 'Markdown: **fet**, *kursiv*, # Overskrift, - liste, > sitat, ![alt](uploads/bilde.jpg).',
        'publish' => 'Publiser nå',
        'save_draft' => '💾 Lagre kladd',
        'cancel' => 'Avbryt',
        'link_url' => 'URL',
        'link_text' => 'Tekst',
        'insert_link' => 'Sett inn',
        'image_url' => 'Bilde-URL',
        'alt_text' => 'Alt-tekst',
        'insert_image' => 'Sett inn',
        'insert_as' => 'Sett inn bilde som',
        'full_width' => '🖼 Full bredde',
        'float_right' => '➡️ Flyt høyre',
        'float_left' => '⬅️ Flyt venstre',
        'centered' => '⬇️ Sentrert',
        'stats_title' => 'Statistikk',
        'stats_total' => 'Totalt %d visninger. Egne besøk logges ikke.',
        'stats_post' => 'Innlegg',
        'stats_date' => 'Dato',
        'stats_views' => 'Visninger',
        'login_title' => 'Logg inn',
        'password_label' => 'Passord',
        'login_error' => 'Feil passord.',
        'login_button' => 'Logg inn',
        'not_found' => 'Fant ikke innlegget',
        'draft_badge' => 'Kladd',
        'powered_by' => 'Powered by tPress',
    ],
    'en' => [
        'home' => 'Home',
        'new_post' => 'New post',
        'stats' => 'Statistics',
        'login' => 'Log in',
        'logout' => 'Log out',
        'logout_confirm' => 'Log out?',
        'search_placeholder' => 'Search posts and tags…',
        'search_button' => 'Search',
        'latest_tags' => 'Latest tags',
        'showing_tag' => 'Showing tag',
        'show_all' => 'Show all',
        'search_results' => 'Search: "%s" · %d results ·',
        'no_posts' => 'No posts to display.',
        'read_more' => 'Read more',
        'views' => 'views',
        'edit' => '✏️ Edit',
        'delete' => '🗑 Delete',
        'delete_confirm' => 'Delete post?',
        'back' => '← Back',
        'back_to_home' => '← Back to home',
        'edit_post' => 'Edit post',
        'new_post_title' => 'New post',
        'upload_media' => 'Upload image, audio or video',
        'upload_button' => 'Upload',
        'select_file' => 'Please select a file first.',
        'uploading' => 'Uploading…',
        'upload_success' => 'Uploaded and inserted into text.',
        'upload_error' => 'Upload failed.',
        'network_error' => 'Network error.',
        'media_audio_video' => 'Audio and video (click to insert):',
        'previous_images' => 'Previous images:',
        'title_label' => 'Title',
        'tags_label' => 'Tags (comma-separated)',
        'tags_hint' => 'Separate tags with commas. Tags appear on the front page and are used as categories.',
        'edit_tab' => 'Edit text',
        'preview_tab' => '👁 Preview',
        'loading' => 'Loading…',
        'empty_preview' => 'Empty.',
        'preview_error' => 'Preview error.',
        'markdown_hint' => 'Markdown: **bold**, *italic*, # Heading, - list, > quote, ![alt](uploads/image.jpg).',
        'publish' => 'Publish now',
        'save_draft' => '💾 Save draft',
        'cancel' => 'Cancel',
        'link_url' => 'URL',
        'link_text' => 'Text',
        'insert_link' => 'Insert',
        'image_url' => 'Image URL',
        'alt_text' => 'Alt text',
        'insert_image' => 'Insert',
        'insert_as' => 'Insert image as',
        'full_width' => '🖼 Full width',
        'float_right' => '➡️ Float right',
        'float_left' => '⬅️ Float left',
        'centered' => '⬇️ Centered',
        'stats_title' => 'Statistics',
        'stats_total' => 'Total %d views. Your own visits are not logged.',
        'stats_post' => 'Post',
        'stats_date' => 'Date',
        'stats_views' => 'Views',
        'login_title' => 'Log in',
        'password_label' => 'Password',
        'login_error' => 'Wrong password.',
        'login_button' => 'Log in',
        'not_found' => 'Post not found',
        'draft_badge' => 'Draft',
        'powered_by' => 'Powered by tPress',
    ]
];

$lang = defined('SITE_LANG') ? SITE_LANG : 'no';
if (!isset($translations[$lang])) $lang = 'no';

function t($key) {
    global $translations, $lang;
    return $translations[$lang][$key] ?? $key;
}

/* ------------------------------------------------------------------ *
 * HJELPEFUNKSJONER
 * ------------------------------------------------------------------ */
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

function e($s) {
    return htmlspecialchars($s !== null ? (string)$s : '', ENT_QUOTES, 'UTF-8');
}

function ensureDirectories() {
    foreach ([POSTS_DIR, UPLOADS_DIR, VIEWS_DIR] as $dir) {
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
    }
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function checkCsrfToken() {
    $sent = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$sent)) {
        http_response_code(403);
        exit('Invalid request (CSRF check failed).');
    }
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function requireLogin() {
    if (empty($_SESSION['logged_in'])) {
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?page=login');
        exit;
    }
}

/* ------------------------------------------------------------------ *
 * TAGGER
 * ------------------------------------------------------------------ */
function parseTags($raw) {
    $tags = [];
    foreach (explode(',', (string)$raw) as $tag) {
        $tag = trim(mb_strtolower($tag, 'UTF-8'));
        $tag = preg_replace('/\s+/u', ' ', $tag);
        if ($tag !== '' && mb_strlen($tag) <= 40 && !in_array($tag, $tags, true)) {
            $tags[] = $tag;
        }
    }
    return $tags;
}

function tagsToString(array $tags) {
    return implode(', ', $tags);
}

function renderTags(array $tags, $currentTag = null) {
    if (!$tags) return '';
    $out = '<div class="tags">';
    foreach ($tags as $tag) {
        $active = ($currentTag !== null && mb_strtolower($currentTag, 'UTF-8') === $tag);
        $out .= '<a class="tag' . ($active ? ' active' : '') . '" href="index.php?tag='
              . urlencode($tag) . '">#' . e($tag) . '</a>';
    }
    return $out . '</div>';
}

function getAllTags(array $posts) {
    $all = [];
    foreach ($posts as $p) {
        foreach ($p['tags'] as $tag) {
            if (!isset($all[$tag])) {
                $all[$tag] = ['count' => 0, 'last_used' => $p['date']];
            }
            $all[$tag]['count']++;
            if (strcmp($p['date'], $all[$tag]['last_used']) > 0) {
                $all[$tag]['last_used'] = $p['date'];
            }
        }
    }
    uasort($all, function ($a, $b) {
        return strcmp($b['last_used'], $a['last_used']);
    });
    return $all;
}

/* ------------------------------------------------------------------ *
 * INNLEGG
 * ------------------------------------------------------------------ */
function readPostFile($file) {
    $raw = str_replace("\r\n", "\n", (string)@file_get_contents($file));
    $lines = explode("\n", $raw);

    $title = trim($lines[0] ?? 'Uten tittel');
    $date  = trim($lines[1] ?? date('Y-m-d H:i'));

    $tags = [];
    $idx = 2;
    if (isset($lines[2]) && str_starts_with(trim($lines[2]), 'tags:')) {
        $tags = parseTags(substr(trim($lines[2]), 5));
        $idx = 3;
    }

    $potentialStatus = trim($lines[$idx] ?? '');
    if ($potentialStatus === 'draft' || $potentialStatus === 'published') {
        $status = $potentialStatus;
        $bodyLines = array_slice($lines, $idx + 1);
    } else {
        $status = 'published';
        $bodyLines = array_slice($lines, $idx);
    }

    return [
        'title' => $title, 'date' => $date, 'status' => $status,
        'tags' => $tags, 'body' => trim(implode("\n", $bodyLines))
    ];
}

function getAllPosts($includeDrafts = false) {
    ensureDirectories();
    $posts = [];
    $files = glob(POSTS_DIR . '/*.txt') ?: [];

    foreach ($files as $file) {
        $p = readPostFile($file);
        if (!$includeDrafts && $p['status'] === 'draft') continue;
        $posts[] = array_merge(['slug' => basename($file, '.txt')], $p);
    }

    usort($posts, function ($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    return $posts;
}

function getPost($slug) {
    $cleanSlug = basename((string)$slug);
    if ($cleanSlug === '' || str_contains($cleanSlug, "\0")) return null;
    $file = POSTS_DIR . '/' . $cleanSlug . '.txt';
    if (!is_file($file)) return null;
    return array_merge(['slug' => $cleanSlug], readPostFile($file));
}

function postSlug($title) {
    $clean = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($title)));
    $clean = trim(mb_substr($clean, 0, 40), '-');
    return date('Y-m-d-His') . '-' . ($clean !== '' ? $clean : 'post');
}

function savePost($title, $body, $tags = '', $status = 'published') {
    ensureDirectories();
    $slug = postSlug($title);
    $content = $title . "\n" . date('Y-m-d H:i') . "\n"
             . 'tags: ' . tagsToString(parseTags($tags)) . "\n"
             . $status . "\n" . $body;
    file_put_contents(POSTS_DIR . '/' . $slug . '.txt', $content, LOCK_EX);
    return $slug;
}

function updatePost($slug, $title, $body, $tags = '', $status = 'published') {
    $file = POSTS_DIR . '/' . basename((string)$slug) . '.txt';
    if (!is_file($file)) return false;
    $old = readPostFile($file);
    $content = $title . "\n" . $old['date'] . "\n"
             . 'tags: ' . tagsToString(parseTags($tags)) . "\n"
             . $status . "\n" . $body;
    return file_put_contents($file, $content, LOCK_EX) !== false;
}

function deletePost($slug) {
    $base = basename((string)$slug);
    $file = POSTS_DIR . '/' . $base . '.txt';
    $deleted = is_file($file) && @unlink($file);
    $viewsFile = VIEWS_DIR . '/' . $base . '.count';
    if ($deleted && is_file($viewsFile)) @unlink($viewsFile);
    return $deleted;
}

/* ------------------------------------------------------------------ *
 * VISNINGSTELLING
 * ------------------------------------------------------------------ */
function viewsFile($slug) {
    return VIEWS_DIR . '/' . basename((string)$slug) . '.count';
}

function recordView($slug) {
    ensureDirectories();
    if (!empty($_SESSION['logged_in'])) return;

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if ($ua === '' || preg_match('/bot|crawl|spider|slurp|curl|wget|monitor|headless/i', $ua)) return;

    $key = 'viewed_' . basename((string)$slug);
    if (!empty($_SESSION[$key])) return;
    $_SESSION[$key] = true;

    $file = viewsFile($slug);
    $count = is_file($file) ? (int)@file_get_contents($file) : 0;
    @file_put_contents($file, (string)($count + 1), LOCK_EX);
}

function getViews($slug) {
    $file = viewsFile($slug);
    return is_file($file) ? (int)@file_get_contents($file) : 0;
}

/* ------------------------------------------------------------------ *
 * OPPLASTING
 * ------------------------------------------------------------------ */
function handleMediaUpload($file) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return null;

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $audioExt = ['mp3', 'm4a', 'ogg', 'wav'];
    $videoExt = ['mp4', 'webm', 'm4v', 'mov'];

    if (in_array($ext, $imageExt, true)) {
        $maxBytes = MAX_UPLOAD_BYTES;
        $validMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    } elseif (in_array($ext, $audioExt, true)) {
        $maxBytes = MAX_MEDIA_UPLOAD_BYTES;
        $validMime = ['audio/mpeg', 'audio/mp4', 'audio/x-m4a', 'audio/ogg',
                      'audio/wav', 'audio/x-wav', 'audio/vnd.wave', 'application/ogg'];
    } elseif (in_array($ext, $videoExt, true)) {
        $maxBytes = MAX_MEDIA_UPLOAD_BYTES;
        $validMime = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-m4v', 'application/ogg'];
    } else {
        return null;
    }

    if (($file['size'] ?? 0) > $maxBytes) return null;

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (!in_array($finfo->file($file['tmp_name']), $validMime, true)) return null;

    ensureDirectories();
    $safeName = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $targetPath = UPLOADS_DIR . '/' . $safeName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        @chmod($targetPath, 0644);
        return UPLOADS_WEB_PATH . $safeName;
    }
    return null;
}

function getUploadedMedia() {
    ensureDirectories();
    $media = [];
    foreach (scandir(UPLOADS_DIR) ?: [] as $f) {
        if ($f === '.' || $f === '..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp3', 'm4a', 'ogg', 'wav'], true)) {
            $media[] = ['path' => UPLOADS_WEB_PATH . $f, 'type' => 'audio', 'name' => $f];
        } elseif (in_array($ext, ['mp4', 'webm', 'm4v', 'mov'], true)) {
            $media[] = ['path' => UPLOADS_WEB_PATH . $f, 'type' => 'video', 'name' => $f];
        }
    }
    return array_reverse($media);
}

function getUploadedImages() {
    ensureDirectories();
    $images = [];
    foreach (scandir(UPLOADS_DIR) ?: [] as $f) {
        if ($f === '.' || $f === '..') continue;
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $images[] = UPLOADS_WEB_PATH . $f;
        }
    }
    rsort($images);
    return $images;
}

/* ------------------------------------------------------------------ *
 * MARKDOWN
 * ------------------------------------------------------------------ */
function md($text) {
    $media = [];
    $text = mediaSyntaxToPlaceholders($text, $media);
    $html = mdFallback($text);
    return mediaPlaceholdersToHtml($html, $media);
}

function mdFallback($text) {
    $text = str_replace("\r\n", "\n", (string)$text);
    $text = e($text);

    $blocks = [];
    $text = preg_replace_callback('/```([a-z]*)\n(.*?)```/s', function ($m) use (&$blocks) {
        $blocks[] = '<pre><code>' . trim($m[2]) . '</code></pre>';
        return "\x00B" . (count($blocks) - 1) . "\x00";
    }, $text);

    $inList = false;
    $inQuote = false;
    $out = '';

    foreach (explode("\n", $text) as $line) {
        if (preg_match('/^\x00B(\d+)\x00$/', trim($line), $m)) {
            if ($inList)  { $out .= "</ul>\n"; $inList = false; }
            if ($inQuote) { $out .= "</blockquote>\n"; $inQuote = false; }
            $out .= $blocks[(int)$m[1]] . "\n";
            continue;
        }

        if (trim($line) === '') {
            if ($inList)  { $out .= "</ul>\n"; $inList = false; }
            if ($inQuote) { $out .= "</blockquote>\n"; $inQuote = false; }
            continue;
        }

        if (preg_match('/^(#{1,3})\s+(.*)$/', $line, $m)) {
            if ($inList)  { $out .= "</ul>\n"; $inList = false; }
            if ($inQuote) { $out .= "</blockquote>\n"; $inQuote = false; }
            $level = strlen($m[1]) + 2;
            $out .= "<h{$level}>" . inline($m[2]) . "</h{$level}>\n";
            continue;
        }

        if (str_starts_with($line, '&gt;')) {
            if ($inList) { $out .= "</ul>\n"; $inList = false; }
            if (!$inQuote) { $out .= "<blockquote>\n"; $inQuote = true; }
            $out .= '<p>' . inline(trim(substr($line, 4))) . "</p>\n";
            continue;
        } elseif ($inQuote) {
            $out .= "</blockquote>\n";
            $inQuote = false;
        }

        if (preg_match('/^[-*]\s+(.*)$/', $line, $m)) {
            if (!$inList) { $out .= "<ul>\n"; $inList = true; }
            $out .= '<li>' . inline($m[1]) . "</li>\n";
            continue;
        } elseif ($inList) {
            $out .= "</ul>\n";
            $inList = false;
        }

        $out .= '<p>' . inline($line) . "</p>\n";
    }

    if ($inList) $out .= "</ul>\n";
    if ($inQuote) $out .= "</blockquote>\n";
    return $out;
}

function inline($s) {
    $s = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/',
        '<img src="$2" alt="$1" loading="lazy" style="max-width:100%; border-radius:6px; margin:.8rem 0;">', $s);
    $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
    $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
    $s = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $s);
    $s = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/',
        '<a href="$2" rel="noopener noreferrer">$1</a>', $s);
    return $s;
}

function mediaSyntaxToPlaceholders($text, array &$media) {
    $patterns = [
        '/\[audio:\s*([^\]\s]+)\s*\]/i' => function ($m) {
            return '<audio controls preload="metadata" src="' . e($m[1]) . '" style="width:100%; margin:.8rem 0;"></audio>';
        },
        '/\[video:\s*([^\]\s]+)\s*\]/i' => function ($m) {
            return '<video controls preload="metadata" src="' . e($m[1]) . '" style="width:100%; border-radius:6px; margin:.8rem 0;"></video>';
        },
        '/\[embed:\s*(https?:\/\/[^\]\s]+)\s*\]/i' => function ($m) {
            return embedHtml($m[1]);
        },
        '/\[img-right:\s*([^\]\s]+)\s*\]/i' => function ($m) {
            return '<figure class="img-float img-float-right"><img src="' . e($m[1]) . '" alt="" loading="lazy"></figure>';
        },
        '/\[img-left:\s*([^\]\s]+)\s*\]/i' => function ($m) {
            return '<figure class="img-float img-float-left"><img src="' . e($m[1]) . '" alt="" loading="lazy"></figure>';
        },
        '/\[img-center:\s*([^\]\s]+)\s*\]/i' => function ($m) {
            return '<figure class="img-float img-float-center"><img src="' . e($m[1]) . '" alt="" loading="lazy"></figure>';
        },
    ];

    foreach ($patterns as $pattern => $callback) {
        $text = preg_replace_callback($pattern, function ($m) use (&$media, $callback) {
            $media[] = $callback($m);
            return '%%MEDIA' . (count($media) - 1) . '%%';
        }, $text);
    }
    return $text;
}

function mediaPlaceholdersToHtml($html, array $media) {
    foreach ($media as $i => $tag) {
        $html = str_replace('%%MEDIA' . $i . '%%', $tag, $html);
    }
    return $html;
}

function embedHtml($url) {
    if (preg_match('#^(?:https?://)?(?:www\.)?(?:youtube\.com/watch\?v=|youtu\.be/|youtube-nocookie\.com/embed/)([A-Za-z0-9_-]{6,})#i', $url, $m)) {
        return '<iframe src="https://www.youtube-nocookie.com/embed/' . e($m[1])
             . '" style="width:100%; aspect-ratio:16/9; border:0; border-radius:6px; margin:.8rem 0;"'
             . ' allowfullscreen loading="lazy" title="Embedded video"></iframe>';
    }
    if (preg_match('#^(?:https?://)?(?:www\.)?vimeo\.com/([0-9]+)#i', $url, $m)) {
        return '<iframe src="https://player.vimeo.com/video/' . e($m[1])
             . '" style="width:100%; aspect-ratio:16/9; border:0; border-radius:6px; margin:.8rem 0;"'
             . ' allowfullscreen loading="lazy" title="Embedded video"></iframe>';
    }
    return '<p><a href="' . e($url) . '" rel="noopener noreferrer">Embedded content: ' . e($url) . '</a></p>';
}

/* ------------------------------------------------------------------ *
 * ROUTING
 * ------------------------------------------------------------------ */
$page = $_GET['page'] ?? 'home';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($page === 'login') {
        if (password_verify($_POST['password'] ?? '', PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
        } else {
            usleep(500000);
            header('Location: ?page=login&error=1');
        }
        exit;
    }

    if (in_array($page, ['upload', 'preview', 'new', 'edit', 'delete', 'logout'], true)) {
        requireLogin();
        checkCsrfToken();
    }

    if ($page === 'upload') {
        header('Content-Type: application/json; charset=UTF-8');
        if (!empty($_FILES['image'])) {
            $path = handleMediaUpload($_FILES['image']);
            if ($path !== null) {
                echo json_encode(['success' => true, 'path' => $path]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'error' => 'Upload failed.']);
        exit;
    }

    if ($page === 'preview') {
        header('Content-Type: text/html; charset=UTF-8');
        echo md($_POST['body'] ?? '');
        exit;
    }

    if ($page === 'new') {
        $status = (($_POST['action'] ?? '') === 'draft') ? 'draft' : 'published';
        $slug = savePost(trim($_POST['title'] ?? ''), trim($_POST['body'] ?? ''), $_POST['tags'] ?? '', $status);
        header('Location: ?page=post&slug=' . urlencode($slug));
        exit;
    }

    if ($page === 'edit') {
        $slug = basename($_POST['slug'] ?? '');
        $status = (($_POST['action'] ?? '') === 'draft') ? 'draft' : 'published';
        if (updatePost($slug, trim($_POST['title'] ?? ''), trim($_POST['body'] ?? ''), $_POST['tags'] ?? '', $status)) {
            header('Location: ?page=post&slug=' . urlencode($slug));
        } else {
            header('Location: index.php');
        }
        exit;
    }

    if ($page === 'delete') {
        deletePost($_POST['slug'] ?? '');
        header('Location: index.php');
        exit;
    }

    if ($page === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }
}

$posts = null;
$post = null;
$editPost = null;
$currentTag = trim($_GET['tag'] ?? '');
$searchQuery = trim($_GET['s'] ?? '');
$siteTitle = defined('SITE_TITLE') ? SITE_TITLE : 'My Blog';

if ($page === 'post') {
    $post = getPost($_GET['slug'] ?? '');
    if (!$post || ($post['status'] === 'draft' && empty($_SESSION['logged_in']))) {
        http_response_code(404);
        $page = '404';
    } else {
        recordView($post['slug']);
    }
} elseif ($page === 'stats') {
    requireLogin();
    $statsPosts = array_map(function ($p) {
        $p['views'] = getViews($p['slug']);
        return $p;
    }, getAllPosts(true));
    usort($statsPosts, function ($a, $b) {
        return $a['views'] === $b['views'] ? strcmp($b['date'], $a['date']) : $b['views'] <=> $a['views'];
    });
    $totalViews = array_sum(array_column($statsPosts, 'views'));
} elseif ($page === 'edit') {
    requireLogin();
    $editPost = getPost($_GET['slug'] ?? '');
    if (!$editPost) {
        http_response_code(404);
        $page = '404';
    }
} elseif ($page !== 'login' && $page !== 'new') {
    $page = 'home';
    $posts = getAllPosts(!empty($_SESSION['logged_in']));
    $allTags = getAllTags($posts);

    if ($currentTag !== '') {
        $currentTag = mb_strtolower($currentTag, 'UTF-8');
        $posts = array_values(array_filter($posts, function ($p) use ($currentTag) {
            return in_array($currentTag, $p['tags'], true);
        }));
    }

    if ($searchQuery !== '') {
        $needle = mb_strtolower($searchQuery, 'UTF-8');
        $posts = array_values(array_filter($posts, function ($p) use ($needle) {
            return mb_strpos(mb_strtolower($p['title'], 'UTF-8'), $needle) !== false
                || mb_strpos(mb_strtolower($p['body'], 'UTF-8'), $needle) !== false
                || in_array($needle, $p['tags'], true);
        }));
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= ($page === 'post' && !empty($post['title'])) ? e($post['title']) : e($siteTitle) ?></title>
<style>
:root{--ink:#1f2328;--muted:#6e7781;--line:#e4e4e7;--bg:#fdfcfa;--accent:#0a6c4a;--accent-ink:#085c3e;--code-bg:#f3f2ef;--draft-bg:#fff8e6;--draft-border:#f59e0b;--draft-text:#b45309;--danger:#b00020;--tag-bg:#eef4f1}
@media(prefers-color-scheme:dark){:root{--ink:#e6e4e0;--muted:#9198a1;--line:#31343b;--bg:#17181b;--accent:#3ecf8e;--accent-ink:#2eb377;--code-bg:#22242a;--draft-bg:#2c2209;--draft-border:#b45309;--draft-text:#fcd34d;--danger:#ff6b6b;--tag-bg:#20281f}}
*{box-sizing:border-box}
body{font-family:Georgia,'Times New Roman',serif;background:var(--bg);color:var(--ink);max-width:700px;margin:0 auto;padding:2.5rem 1.4rem 4rem;line-height:1.7;font-size:1.05rem}
header{margin-bottom:3rem}
header h1{margin:0;font-size:2.1rem;font-family:'Helvetica Neue',Arial,sans-serif;font-weight:800}
header h1 a{color:var(--ink);text-decoration:none}
header h1 a:hover{color:var(--accent)}
nav{margin-top:.8rem;display:flex;align-items:center;gap:1.4rem;flex-wrap:wrap}
nav a,nav form button.link-like{color:var(--muted);text-decoration:none;font-size:.88rem;font-family:'Helvetica Neue',Arial,sans-serif;text-transform:uppercase;letter-spacing:.08em;background:none;border:none;padding:0;margin:0;cursor:pointer}
nav a:hover{color:var(--accent)}
article{margin-bottom:3rem}
h2{margin:0 0 .25rem;font-size:1.5rem;line-height:1.3;font-family:'Helvetica Neue',Arial,sans-serif}
h2 a{color:var(--ink);text-decoration:none}
h2 a:hover{color:var(--accent)}
.date{color:var(--muted);font-size:.8rem;margin-bottom:.8rem;font-family:'Helvetica Neue',Arial,sans-serif;text-transform:uppercase}
.badge{display:inline-block;font-size:.75rem;padding:.15rem .5rem;border-radius:4px;background:var(--draft-bg);border:1px solid var(--draft-border);color:var(--draft-text);font-weight:bold;vertical-align:middle;margin-left:.5rem;text-transform:uppercase}
.body p{margin:.9rem 0}
.body blockquote{border-left:3px solid var(--accent);margin:1rem 0;padding:.2rem 1.2rem;color:var(--muted);font-style:italic}
.body pre{background:var(--code-bg);padding:1rem 1.2rem;border-radius:6px;overflow-x:auto;font-size:.88em}
.body code{background:var(--code-bg);padding:.15rem .35rem;border-radius:4px;font-size:.88em}
.body pre code{background:none;padding:0}
.body img{max-width:100%;height:auto;display:block;border-radius:6px}
.body audio,.body video{width:100%}
.hint{color:var(--muted);font-size:.85rem;line-height:1.5;margin-top:.5rem}
.error{color:var(--danger);font-weight:bold}
.admin-actions{margin:1.2rem 0;display:flex;gap:1rem;align-items:center}
input[type=text],input[type=password],textarea{width:100%;padding:.7rem .9rem;font:inherit;border:1px solid var(--line);border-radius:6px;background:var(--bg);color:var(--ink)}
textarea{min-height:240px}
button{margin-top:1.2rem;padding:.65rem 1.4rem;background:var(--accent);color:#fff;border:none;border-radius:6px;font-family:'Helvetica Neue',Arial,sans-serif;cursor:pointer;font-size:.95rem}
button.secondary{background:transparent;color:var(--ink);border:1px solid var(--line)}
button.danger{background:transparent;color:var(--danger);border:1px solid var(--danger);padding:.35rem 1rem;margin-top:0}
hr{border:none;border-top:1px solid var(--line);margin:2.5rem 0}
.search-box{margin:0 0 2rem;display:flex;gap:.5rem}
.search-box input[type=text]{flex:1}
.search-box button{margin-top:0;padding:.55rem 1.1rem}
.tags{display:flex;flex-wrap:wrap;gap:.4rem;margin:.3rem 0 .8rem}
.tag{display:inline-block;font-size:.78rem;padding:.14rem .55rem;border-radius:999px;background:var(--tag-bg);color:var(--accent-ink);text-decoration:none;font-family:'Helvetica Neue',Arial,sans-serif;border:1px solid var(--line)}
.tag.active{background:var(--accent);border-color:var(--accent);color:#fff}
.tag-cloud{margin:0 0 2.5rem}
.tag-cloud h3{margin:0 0 .5rem;font-size:.8rem;color:var(--muted);font-family:'Helvetica Neue',Arial,sans-serif;text-transform:uppercase;letter-spacing:.08em}
.filter-info{margin:0 0 1.5rem;font-size:.9rem;color:var(--muted)}
.filter-info a{color:var(--accent)}
.upload-box{border:1px dashed var(--line);padding:1rem;border-radius:6px;margin:1.5rem 0;background:var(--code-bg)}
.thumb-list{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.5rem}
.thumb-list img{width:70px;height:70px;object-fit:cover;border-radius:4px;border:1px solid var(--line);cursor:pointer}
.media-list{display:flex;flex-direction:column;gap:.4rem;margin-top:.5rem}
.media-item{margin-top:0;padding:.4rem .8rem;font-size:.85rem;text-align:left;background:var(--bg);color:var(--ink);border:1px solid var(--line);border-radius:6px;cursor:pointer;font-family:'Helvetica Neue',Arial,sans-serif}
.tab-buttons{display:flex;gap:.5rem;margin-top:1rem;border-bottom:1px solid var(--line)}
.tab-buttons button{margin-top:0;border-radius:6px 6px 0 0;background:none;color:var(--muted);border:1px solid transparent;border-bottom:none;padding:.5rem 1rem}
.tab-buttons button.active{background:var(--bg);color:var(--ink);border-color:var(--line);font-weight:bold}
#previewContainer{display:none;padding:1.2rem;border:1px solid var(--line);border-top:none;background:var(--bg);border-radius:0 0 6px 6px;min-height:240px}
.form-actions{display:flex;gap:.8rem;align-items:center;flex-wrap:wrap;margin-top:1.2rem}
.stats-table{width:100%;border-collapse:collapse;font-size:.9rem;margin-top:1rem;font-family:'Helvetica Neue',Arial,sans-serif}
.stats-table th,.stats-table td{text-align:left;padding:.6rem .5rem;border-bottom:1px solid var(--line);vertical-align:top}
.stats-table th{color:var(--muted);font-size:.75rem;text-transform:uppercase;letter-spacing:.08em}
.stats-table td.num{text-align:right;font-weight:bold}
.img-float{margin:.4rem 0;padding:0;border:none}
.img-float img{display:block;border-radius:6px;max-width:100%;height:auto}
.img-float-right{float:right;margin:.2rem 0 .8rem 1.2rem;max-width:50%}
.img-float-left{float:left;margin:.2rem 1.2rem .8rem 0;max-width:50%}
.img-float-center{display:block;margin:1rem auto;text-align:center;float:none;max-width:100%}
.body::after{content:"";display:table;clear:both}
@media(max-width:520px){.img-float-right,.img-float-left{float:none;max-width:100%;margin:.8rem 0}}
.editor-toolbar{display:flex;flex-wrap:wrap;gap:.3rem;padding:.5rem;background:var(--code-bg);border:1px solid var(--line);border-bottom:none;border-radius:6px 6px 0 0}
.editor-toolbar button{margin:0;padding:.4rem .7rem;background:var(--bg);color:var(--ink);border:1px solid var(--line);border-radius:4px;font-size:.85rem;cursor:pointer;font-weight:600;min-width:2.2rem}
.editor-toolbar .toolbar-divider{width:1px;background:var(--line);margin:0 .3rem}
.editor-toolbar .toolbar-dropdown{position:relative}
.dropdown-menu{display:none;position:absolute;top:100%;left:0;background:var(--bg);border:1px solid var(--line);border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.1);z-index:100;min-width:180px;margin-top:.3rem}
.dropdown-menu.show{display:block}
.dropdown-menu button{display:block;width:100%;text-align:left;border:none;border-radius:0;padding:.6rem 1rem;font-weight:normal}
.editor-popup{position:fixed;z-index:1000;background:var(--bg);border:1px solid var(--line);border-radius:8px;padding:1rem;box-shadow:0 4px 16px rgba(0,0,0,.15);display:none;font-family:'Helvetica Neue',Arial,sans-serif;font-size:.9rem;min-width:300px}
.editor-popup.show{display:block}
.editor-popup label{display:block;margin-bottom:.3rem;font-weight:600;font-size:.85rem}
.editor-popup input[type=text]{padding:.5rem;margin-bottom:.8rem;font-size:.9rem}
.popup-actions{display:flex;gap:.5rem;justify-content:flex-end}
.editor-popup button{margin:0;padding:.5rem 1rem;font-size:.85rem}
.img-choice-popup{position:fixed;z-index:1000;background:var(--bg);border:1px solid var(--line);border-radius:8px;padding:.5rem;box-shadow:0 4px 16px rgba(0,0,0,.15);display:flex;flex-direction:column;gap:.3rem;font-family:'Helvetica Neue',Arial,sans-serif;font-size:.85rem}
.img-choice-popup .choice-title{font-size:.75rem;color:var(--muted);text-transform:uppercase;padding:.2rem .4rem}
.img-choice-popup button{margin:0;padding:.4rem .8rem;background:transparent;color:var(--ink);border:none;border-radius:4px;text-align:left;font-size:.85rem;cursor:pointer}
</style>
</head>
<body>

<header>
  <h1><a href="index.php"><?= e($siteTitle) ?></a></h1>
  <p class="tagline"><?= $lang === 'no' ? 'Om å "tegne med lys".' : 'About "drawing with light".' ?></p>
  <nav>
    <a href="index.php"><?= t('home') ?></a>
    <?php if (!empty($_SESSION['logged_in'])): ?>
      <a href="?page=new"><?= t('new_post') ?></a>
      <a href="?page=stats"><?= t('stats') ?></a>
      <form method="post" action="?page=logout" onsubmit="return confirm('<?= t('logout_confirm') ?>');">
        <?= csrfField() ?>
        <button type="submit" class="link-like"><?= t('logout') ?></button>
      </form>
    <?php else: ?>
      <a href="?page=login"><?= t('login') ?></a>
    <?php endif; ?>
  </nav>
</header>

<?php if ($page === 'home'): ?>
  <form method="get" action="index.php" class="search-box">
    <input type="text" name="s" placeholder="<?= t('search_placeholder') ?>" value="<?= e($searchQuery) ?>">
    <button type="submit"><?= t('search_button') ?></button>
  </form>

  <?php if (!empty($allTags)): ?>
    <div class="tag-cloud">
      <h3><?= t('latest_tags') ?></h3>
      <div class="tags">
        <?php $i = 0; foreach ($allTags as $tagName => $info): ?>
          <?php if ($i++ >= 15) break; ?>
          <a class="tag<?= ($tagName === $currentTag) ? ' active' : '' ?>"
             href="index.php?tag=<?= urlencode($tagName) ?>"><?= e($tagName) ?> (<?= (int)$info['count'] ?>)</a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($currentTag !== ''): ?>
    <p class="filter-info"><?= t('showing_tag') ?> <strong>#<?= e($currentTag) ?></strong> · <a href="index.php"><?= t('show_all') ?></a></p>
  <?php elseif ($searchQuery !== ''): ?>
    <p class="filter-info"><?= sprintf(t('search_results'), e($searchQuery), count($posts)) ?> <a href="index.php"><?= t('show_all') ?></a></p>
  <?php endif; ?>

  <?php if (!$posts): ?><p><?= t('no_posts') ?></p><?php endif; ?>

  <?php foreach ($posts as $p): ?>
    <article>
      <h2><a href="?page=post&slug=<?= urlencode($p['slug']) ?>"><?= e($p['title']) ?></a>
        <?php if ($p['status'] === 'draft'): ?><span class="badge"><?= t('draft_badge') ?></span><?php endif; ?>
      </h2>
      <div class="date"><?= e($p['date']) ?></div>
      <?= renderTags($p['tags'], $currentTag !== '' ? $currentTag : null) ?>
      <div class="body">
        <?php if (mb_strlen($p['body']) > 300): ?>
          <p><?= e(mb_substr($p['body'], 0, 300)) ?> …
            <a href="?page=post&slug=<?= urlencode($p['slug']) ?>"><?= t('read_more') ?></a></p>
        <?php else: ?>
          <?= md($p['body']) ?>
        <?php endif; ?>
      </div>
    </article>
    <hr>
  <?php endforeach; ?>

<?php elseif ($page === 'post' && $post): ?>
  <article>
    <h2><?= e($post['title']) ?> <?php if ($post['status'] === 'draft'): ?><span class="badge"><?= t('draft_badge') ?></span><?php endif; ?></h2>
    <div class="date"><?= e($post['date']) ?> · <?= getViews($post['slug']) ?> <?= t('views') ?></div>
    <?= renderTags($post['tags']) ?>
    <div class="body"><?= md($post['body']) ?></div>
    <?php if (!empty($_SESSION['logged_in'])): ?>
      <div class="admin-actions">
        <a href="?page=edit&slug=<?= urlencode($post['slug']) ?>"><?= t('edit') ?></a>
        <form method="post" action="?page=delete" onsubmit="return confirm('<?= t('delete_confirm') ?>');">
          <?= csrfField() ?>
          <input type="hidden" name="slug" value="<?= e($post['slug']) ?>">
          <button type="submit" class="danger"><?= t('delete') ?></button>
        </form>
      </div>
    <?php endif; ?>
    <p><a href="index.php"><?= t('back_to_home') ?></a></p>
  </article>

<?php elseif ($page === 'new' || ($page === 'edit' && $editPost)): ?>
  <?php requireLogin(); ?>
  <h2><?= $page === 'edit' ? t('edit_post') : t('new_post_title') ?></h2>

  <div class="upload-box">
    <strong><?= t('upload_media') ?></strong>
    <div style="margin-top:.5rem;display:flex;gap:.5rem;align-items:center">
      <input type="file" id="imageInput">
      <button type="button" id="uploadBtn" style="margin-top:0;padding:.4rem .8rem;font-size:.85rem"><?= t('upload_button') ?></button>
    </div>
    <p id="uploadStatus" class="hint" style="display:none"></p>

    <?php $uploadedMedia = getUploadedMedia(); if ($uploadedMedia): ?>
      <p class="hint"><?= t('media_audio_video') ?></p>
      <div class="media-list">
        <?php foreach (array_slice($uploadedMedia, 0, 10) as $mf): ?>
          <button type="button" class="media-item"
                  onclick="insertMediaMarkdown('<?= e($mf['path']) ?>','<?= e($mf['type']) ?>')">
            <?= $mf['type'] === 'audio' ? '🎵' : '🎬' ?> <?= e($mf['name']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php $uploaded = getUploadedImages(); if ($uploaded): ?>
      <p class="hint"><?= t('previous_images') ?></p>
      <div class="thumb-list" id="thumbGallery">
        <?php foreach (array_slice($uploaded, 0, 8) as $img): ?>
          <img src="<?= e($img) ?>" alt="" onclick="showImageChoice(event,'<?= e($img) ?>')">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <form method="post" id="postForm" action="?page=<?= $page ?>">
    <?= csrfField() ?>
    <?php if ($page === 'edit'): ?><input type="hidden" name="slug" value="<?= e($editPost['slug']) ?>"><?php endif; ?>

    <label for="title"><?= t('title_label') ?></label>
    <input type="text" id="title" name="title" required maxlength="120"
           value="<?= e($editPost['title'] ?? '') ?>">

    <label for="tags"><?= t('tags_label') ?></label>
    <input type="text" id="tags" name="tags" maxlength="200"
           value="<?= e(tagsToString($editPost['tags'] ?? [])) ?>">
    <p class="hint"><?= t('tags_hint') ?></p>

    <div class="tab-buttons">
      <button type="button" id="editTab" class="active" onclick="switchTab('edit')"><?= t('edit_tab') ?></button>
      <button type="button" id="previewTab" onclick="switchTab('preview')"><?= t('preview_tab') ?></button>
    </div>

    <div id="editorContainer">
      <div class="editor-toolbar">
        <button type="button" onclick="wrapSelection('**','**')"><strong>B</strong></button>
        <button type="button" onclick="wrapSelection('*','*')"><em>I</em></button>
        <button type="button" onclick="wrapSelection('`','`')">&lt;/&gt;</button>
        <div class="toolbar-divider"></div>
        <button type="button" onclick="showLinkPopup()">🔗</button>
        <button type="button" onclick="showImagePopup()">🖼</button>
        <div class="toolbar-divider"></div>
        <div class="toolbar-dropdown">
          <button type="button" onclick="toggleDropdown(this)"><?= $lang === 'no' ? 'Mer ▾' : 'More ▾' ?></button>
          <div class="dropdown-menu">
            <button type="button" onclick="insertAtCursor('\n[audio: uploads/fil.mp3]\n')">🎵 <?= $lang === 'no' ? 'Lyd' : 'Audio' ?></button>
            <button type="button" onclick="insertAtCursor('\n[video: uploads/fil.mp4]\n')">🎬 <?= $lang === 'no' ? 'Video' : 'Video' ?></button>
            <button type="button" onclick="insertAtCursor('\n[embed: https://youtu.be/xxxxx]\n')">📺 <?= $lang === 'no' ? 'Innbygging' : 'Embed' ?></button>
            <button type="button" onclick="insertAtCursor('\n[img-right: uploads/bilde.jpg]\n')">➡️ <?= $lang === 'no' ? 'Flyt høyre' : 'Float right' ?></button>
            <button type="button" onclick="insertAtCursor('\n[img-left: uploads/bilde.jpg]\n')">⬅️ <?= $lang === 'no' ? 'Flyt venstre' : 'Float left' ?></button>
            <button type="button" onclick="insertAtCursor('\n[img-center: uploads/bilde.jpg]\n')">⬇️ <?= $lang === 'no' ? 'Sentrert' : 'Centered' ?></button>
          </div>
        </div>
      </div>
      <textarea id="body" name="body" required><?= e($editPost['body'] ?? '') ?></textarea>
    </div>
    <div id="previewContainer" class="body"></div>

    <p class="hint"><?= t('markdown_hint') ?></p>

    <div class="form-actions">
      <button type="submit" name="action" value="publish"><?= t('publish') ?></button>
      <button type="submit" name="action" value="draft" class="secondary"><?= t('save_draft') ?></button>
      <a href="index.php"><?= t('cancel') ?></a>
    </div>
  </form>

  <div id="linkPopup" class="editor-popup">
    <label for="linkUrl"><?= t('link_url') ?></label>
    <input type="text" id="linkUrl" placeholder="https://example.com">
    <label for="linkText"><?= t('link_text') ?></label>
    <input type="text" id="linkText" placeholder="<?= $lang === 'no' ? 'Klikk her' : 'Click here' ?>">
    <div class="popup-actions">
      <button type="button" class="secondary" onclick="closePopups()"><?= t('cancel') ?></button>
      <button type="button" onclick="insertLink()"><?= t('insert_link') ?></button>
    </div>
  </div>

  <div id="imagePopup" class="editor-popup">
    <label for="imageUrl"><?= t('image_url') ?></label>
    <input type="text" id="imageUrl" placeholder="uploads/image.jpg">
    <label for="imageAlt"><?= t('alt_text') ?></label>
    <input type="text" id="imageAlt" placeholder="<?= $lang === 'no' ? 'Beskrivelse' : 'Description' ?>">
    <div class="popup-actions">
      <button type="button" class="secondary" onclick="closePopups()"><?= t('cancel') ?></button>
      <button type="button" onclick="insertImage()"><?= t('insert_image') ?></button>
    </div>
  </div>

  <script>
    var isNew = <?= ($page === 'new') ? 'true' : 'false' ?>;
    var csrfToken = <?= json_encode(csrfToken()) ?>;
    var titleInput = document.getElementById('title');
    var bodyInput = document.getElementById('body');
    var savedSelection = {start:0, end:0};
    var i18n = {
      select_file: <?= json_encode(t('select_file')) ?>,
      uploading: <?= json_encode(t('uploading')) ?>,
      upload_success: <?= json_encode(t('upload_success')) ?>,
      upload_error: <?= json_encode(t('upload_error')) ?>,
      network_error: <?= json_encode(t('network_error')) ?>,
      loading: <?= json_encode(t('loading')) ?>,
      empty_preview: <?= json_encode(t('empty_preview')) ?>,
      preview_error: <?= json_encode(t('preview_error')) ?>,
      insert_as: <?= json_encode(t('insert_as')) ?>,
      full_width: <?= json_encode(t('full_width')) ?>,
      float_right: <?= json_encode(t('float_right')) ?>,
      float_left: <?= json_encode(t('float_left')) ?>,
      centered: <?= json_encode(t('centered')) ?>
    };

    if (isNew) {
      if (!titleInput.value && localStorage.getItem('draft_title')) titleInput.value = localStorage.getItem('draft_title');
      if (!bodyInput.value && localStorage.getItem('draft_body')) bodyInput.value = localStorage.getItem('draft_body');
      titleInput.addEventListener('input', function(){ localStorage.setItem('draft_title', titleInput.value); });
      bodyInput.addEventListener('input', function(){ localStorage.setItem('draft_body', bodyInput.value); });
      document.getElementById('postForm').addEventListener('submit', function(){
        localStorage.removeItem('draft_title'); localStorage.removeItem('draft_body');
      });
    }

    bodyInput.addEventListener('click', saveSelection);
    bodyInput.addEventListener('keyup', saveSelection);
    function saveSelection(){ savedSelection.start = bodyInput.selectionStart; savedSelection.end = bodyInput.selectionEnd; }

    function wrapSelection(before, after){
      var start = bodyInput.selectionStart, end = bodyInput.selectionEnd, text = bodyInput.value;
      var selected = text.substring(start, end);
      bodyInput.value = text.substring(0,start) + before + selected + after + text.substring(end);
      bodyInput.selectionStart = start + before.length;
      bodyInput.selectionEnd = end + before.length;
      bodyInput.focus();
      if (isNew) localStorage.setItem('draft_body', bodyInput.value);
    }

    function insertAtCursor(markdown){
      var start = bodyInput.selectionStart, end = bodyInput.selectionEnd;
      bodyInput.value = bodyInput.value.substring(0,start) + markdown + bodyInput.value.substring(end);
      bodyInput.focus();
      bodyInput.selectionStart = bodyInput.selectionEnd = start + markdown.length;
      if (isNew) localStorage.setItem('draft_body', bodyInput.value);
    }

    function showLinkPopup(){
      saveSelection();
      document.getElementById('linkText').value = bodyInput.value.substring(savedSelection.start, savedSelection.end);
      document.getElementById('linkUrl').value = '';
      document.getElementById('linkPopup').classList.add('show');
      document.getElementById('linkUrl').focus();
    }
    function showImagePopup(){
      saveSelection();
      document.getElementById('imageUrl').value = '';
      document.getElementById('imageAlt').value = '';
      document.getElementById('imagePopup').classList.add('show');
      document.getElementById('imageUrl').focus();
    }
    function closePopups(){
      document.getElementById('linkPopup').classList.remove('show');
      document.getElementById('imagePopup').classList.remove('show');
      bodyInput.focus();
    }
    function insertLink(){
      var url = document.getElementById('linkUrl').value;
      var text = document.getElementById('linkText').value || 'Link';
      if (url) insertAtCursor('[' + text + '](' + url + ')');
      closePopups();
    }
    function insertImage(){
      var url = document.getElementById('imageUrl').value;
      var alt = document.getElementById('imageAlt').value || 'Image';
      if (url) insertAtCursor('![' + alt + '](' + url + ')');
      closePopups();
    }
    function toggleDropdown(btn){ btn.nextElementSibling.classList.toggle('show'); }

    document.addEventListener('click', function(e){
      if (!e.target.closest('.toolbar-dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(function(m){ m.classList.remove('show'); });
      }
    });

    function switchTab(mode){
      var editor = document.getElementById('editorContainer');
      var preview = document.getElementById('previewContainer');
      document.getElementById('editTab').classList.toggle('active', mode === 'edit');
      document.getElementById('previewTab').classList.toggle('active', mode === 'preview');
      editor.style.display = mode === 'edit' ? 'block' : 'none';
      preview.style.display = mode === 'preview' ? 'block' : 'none';
      if (mode !== 'preview') return;
      preview.innerHTML = '<em>' + i18n.loading + '</em>';
      var fd = new FormData();
      fd.append('body', bodyInput.value);
      fd.append('csrf_token', csrfToken);
      fetch('?page=preview', {method:'POST', body:fd})
        .then(function(r){ return r.text(); })
        .then(function(html){ preview.innerHTML = html || '<em>' + i18n.empty_preview + '</em>'; })
        .catch(function(){ preview.innerHTML = i18n.preview_error; });
    }

    function insertMediaMarkdown(path, type){
      insertAtCursor(type === 'audio' ? '\n[audio: ' + path + ']\n' : '\n[video: ' + path + ']\n');
    }

    var activePopup = null;
    function showImageChoice(event, path){
      event.stopPropagation(); closeImageChoice();
      var popup = document.createElement('div');
      popup.className = 'img-choice-popup';
      popup.innerHTML = '<div class="choice-title">' + i18n.insert_as + '</div>' +
        '<button type="button" data-type="full">' + i18n.full_width + '</button>' +
        '<button type="button" data-type="right">' + i18n.float_right + '</button>' +
        '<button type="button" data-type="left">' + i18n.float_left + '</button>' +
        '<button type="button" data-type="center">' + i18n.centered + '</button>';
      popup.addEventListener('click', function(e){
        var btn = e.target.closest('button[data-type]');
        if (!btn) return;
        var t = btn.getAttribute('data-type');
        var md = t === 'full' ? '\n![Image](' + path + ')\n' : '\n[img-' + t + ': ' + path + ']\n';
        insertAtCursor(md); closeImageChoice();
      });
      document.body.appendChild(popup);
      activePopup = popup;
      var r = event.target.getBoundingClientRect();
      popup.style.top = (r.bottom + window.scrollY + 6) + 'px';
      popup.style.left = Math.min(r.left + window.scrollX, window.innerWidth - 210) + 'px';
    }
    function closeImageChoice(){ if (activePopup) { activePopup.remove(); activePopup = null; } }
    document.addEventListener('click', function(e){ if (activePopup && !activePopup.contains(e.target)) closeImageChoice(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') { closeImageChoice(); closePopups(); } });

    document.getElementById('uploadBtn').addEventListener('click', function(){
      var input = document.getElementById('imageInput');
      var status = document.getElementById('uploadStatus');
      if (!input.files.length) { alert(i18n.select_file); return; }
      var fd = new FormData();
      fd.append('image', input.files[0]);
      fd.append('csrf_token', csrfToken);
      status.style.display = 'block';
      status.textContent = i18n.uploading;
      fetch('?page=upload', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(data){
          if (!data.success) { status.textContent = data.error || i18n.upload_error; return; }
          status.textContent = i18n.upload_success;
          insertAtCursor('\n![Image](' + data.path + ')\n');
          input.value = '';
        })
        .catch(function(){ status.textContent = i18n.network_error; });
    });
  </script>

<?php elseif ($page === 'stats'): ?>
  <h2><?= t('stats_title') ?></h2>
  <p class="hint"><?= sprintf(t('stats_total'), (int)$totalViews) ?></p>
  <table class="stats-table">
    <thead><tr><th><?= t('stats_post') ?></th><th><?= t('stats_date') ?></th><th><?= t('stats_views') ?></th></tr></thead>
    <tbody>
      <?php foreach ($statsPosts as $sp): ?>
        <tr>
          <td><a href="?page=post&slug=<?= urlencode($sp['slug']) ?>"><?= e($sp['title']) ?></a>
            <?php if ($sp['status'] === 'draft'): ?><span class="badge"><?= t('draft_badge') ?></span><?php endif; ?>
          </td>
          <td><?= e($sp['date']) ?></td>
          <td class="num"><?= (int)$sp['views'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php elseif ($page === 'login'): ?>
  <h2><?= t('login_title') ?></h2>
  <?php if (isset($_GET['error'])): ?><p class="error"><?= t('login_error') ?></p><?php endif; ?>
  <form method="post" action="?page=login">
    <label for="password"><?= t('password_label') ?></label>
    <input type="password" id="password" name="password" autofocus>
    <button type="submit"><?= t('login_button') ?></button>
  </form>

<?php elseif ($page === '404'): ?>
  <h2><?= t('not_found') ?></h2>
  <p><a href="index.php"><?= t('back_to_home') ?></a></p>
<?php endif; ?>

<footer style="margin-top:4rem;padding-top:1.5rem;border-top:1px solid var(--line);text-align:center;color:var(--muted);font-size:.85rem">
  <p><?= t('powered_by') ?></p>
</footer>
</body>
</html>
