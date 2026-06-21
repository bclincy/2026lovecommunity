<?php

session_start();
require_once __DIR__ . '/../../bootstrap.php';

$adminUser = $_ENV['ADMIN_USER'] ?? 'admin';
$adminPass = $_ENV['ADMIN_PASS'] ?? 'password';

function loadJsonFile(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    $contents = file_get_contents($path);
    $data = json_decode($contents, true);

    return is_array($data) ? $data : [];
}

function saveJsonFile(string $path, array $data): bool
{
    return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
}

function getContactData(): array
{
    return loadJsonFile(__DIR__ . '/../../data/contacts.json');
}

function saveContactData(array $records): bool
{
    return saveJsonFile(__DIR__ . '/../../data/contacts.json', $records);
}

function getVolunteerData(): array
{
    return loadJsonFile(__DIR__ . '/../../data/volunteers.json');
}

function saveVolunteerData(array $records): bool
{
    return saveJsonFile(__DIR__ . '/../../data/volunteers.json', $records);
}

$loginError = null;
$auth = $_SESSION['admin_authenticated'] ?? false;

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /admin');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($username === $adminUser && $password === $adminPass) {
            $_SESSION['admin_authenticated'] = true;
            header('Location: /admin');
            exit;
        }

        $loginError = 'Invalid username or password.';
    }

    if ($auth) {
        if ($action === 'toggle_contact_status') {
            $index = intval($_POST['index'] ?? -1);
            $records = getContactData();
            if (isset($records[$index])) {
                $records[$index]['responded'] = empty($records[$index]['responded']) ? true : false;
                saveContactData($records);
            }
            header('Location: /admin');
            exit;
        }

        if ($action === 'delete_contact') {
            $index = intval($_POST['index'] ?? -1);
            $records = getContactData();
            if (isset($records[$index])) {
                array_splice($records, $index, 1);
                saveContactData($records);
            }
            header('Location: /admin');
            exit;
        }

        if ($action === 'toggle_volunteer_status') {
            $index = intval($_POST['index'] ?? -1);
            $records = getVolunteerData();
            if (isset($records[$index])) {
                $records[$index]['processed'] = empty($records[$index]['processed']) ? true : false;
                saveVolunteerData($records);
            }
            header('Location: /admin');
            exit;
        }

        if ($action === 'delete_volunteer') {
            $index = intval($_POST['index'] ?? -1);
            $records = getVolunteerData();
            if (isset($records[$index])) {
                array_splice($records, $index, 1);
                saveVolunteerData($records);
            }
            header('Location: /admin');
            exit;
        }
    }
}

$contacts = getContactData();
$volunteers = getVolunteerData();

$contactResponded = count(array_filter($contacts, function ($record) {
    return !empty($record['responded']);
}));
$contactTotal = count($contacts);
$volunteerUnprocessed = count(array_filter($volunteers, function ($record) {
    return empty($record['processed']);
}));
$volunteerTotal = count($volunteers);

function formatDate($timestamp)
{
    if (empty($timestamp)) {
        return 'N/A';
    }

    return date('Y-m-d H:i', intval($timestamp));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <style>
        body { margin: 0; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f5f7; color: #111827; }
        header { background: #0f5132; color: #ffffff; padding: 1rem 1.5rem; }
        header h1 { margin: 0; font-size: 1.5rem; }
        .container { max-width: 1240px; margin: 0 auto; padding: 1.5rem; }
        .card-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 1.5rem; }
        .card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 1.25rem; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06); }
        .card h2 { margin: 0; font-size: 0.875rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
        .card p { margin: 0.75rem 0 0; font-size: 2rem; font-weight: 700; }
        .grid-two { display: grid; gap: 1.5rem; grid-template-columns: 1.35fr 1fr; }
        .table-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .table-card header { padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; background: #fafafa; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 0.9rem 0.75rem; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 0.95rem; }
        table th { background: #f9fafb; color: #374151; font-weight: 700; }
        table tr:last-child td { border-bottom: none; }
        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.8rem; color: #fff; }
        .badge-green { background: #16a34a; }
        .badge-orange { background: #f59e0b; }
        .badge-gray { background: #6b7280; }
        .actions form { display: inline-block; margin: 0 0.15rem; }
        .actions button { border: 0; border-radius: 8px; background: #0f5132; color: #fff; font-size: 0.85rem; padding: 0.5rem 0.65rem; cursor: pointer; }
        .actions button.delete { background: #b91c1c; }
        .login-panel { max-width: 420px; margin: 3rem auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 20px rgba(15, 23, 42, 0.08); }
        .login-panel h2 { margin-top: 0; }
        .login-panel label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .login-panel input { width: 100%; padding: 0.85rem 1rem; border-radius: 12px; border: 1px solid #d1d5db; margin-bottom: 1rem; font-size: 1rem; }
        .login-panel button { width: 100%; padding: 0.95rem 1rem; border-radius: 12px; border: none; background: #0f5132; color: #fff; font-weight: 700; cursor: pointer; }
        .note { margin-top: 0.75rem; color: #dc2626; }
        .top-actions { display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap; }
        .top-actions a { color: #0f5132; text-decoration: none; border: 1px solid #0f5132; padding: 0.65rem 1rem; border-radius: 999px; font-weight: 600; }
    </style>
</head>
<body>
<header>
    <div class="container top-actions">
        <div>
            <h1>Admin Dashboard</h1>
            <p style="margin: .4rem 0 0; color: #d1fae5;">Manage contact submissions and volunteer requests.</p>
        </div>
        <?php if ($auth): ?>
            <a href="?action=logout">Logout</a>
        <?php endif; ?>
    </div>
</header>
<main class="container">
    <?php if (!$auth): ?>
        <section class="login-panel">
            <h2>Admin Login</h2>
            <form method="post">
                <input type="hidden" name="action" value="login" />
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="" autocomplete="username" required />
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required />
                <button type="submit">Sign in</button>
                <?php if ($loginError): ?><p class="note"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
            </form>
        </section>
    <?php else: ?>
        <div class="card-grid">
            <div class="card">
                <h2>Contact forms</h2>
                <p><?= $contactTotal ?></p>
            </div>
            <div class="card">
                <h2>Contact responded</h2>
                <p><?= $contactResponded ?></p>
            </div>
            <div class="card">
                <h2>Volunteer submissions</h2>
                <p><?= $volunteerTotal ?></p>
            </div>
            <div class="card">
                <h2>Volunteer unprocessed</h2>
                <p><?= $volunteerUnprocessed ?></p>
            </div>
        </div>

        <div class="grid-two">
            <section class="table-card">
                <header>Contact submissions</header>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($contacts)): ?>
                            <tr><td colspan="7">No contact submissions yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($contacts as $index => $contact): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars(($contact['fname'] ?? '') . ' ' . ($contact['lname'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($contact['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($contact['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (!empty($contact['responded'])): ?>
                                            <span class="badge badge-green">Responded</span>
                                        <?php else: ?>
                                            <span class="badge badge-orange">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(formatDate($contact['received_at'] ?? $contact['created_at'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="actions">
                                        <form method="post">
                                            <input type="hidden" name="action" value="toggle_contact_status" />
                                            <input type="hidden" name="index" value="<?= $index ?>" />
                                            <button type="submit"><?= empty($contact['responded']) ? 'Mark responded' : 'Mark new' ?></button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="action" value="delete_contact" />
                                            <input type="hidden" name="index" value="<?= $index ?>" />
                                            <button type="submit" class="delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7" style="background:#f9fafb; font-size:.9rem; color:#4b5563;">
                                        <?= nl2br(htmlspecialchars($contact['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section class="table-card">
                <header>Volunteer submissions</header>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($volunteers)): ?>
                            <tr><td colspan="7">No volunteer submissions yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($volunteers as $index => $volunteer): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($volunteer['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($volunteer['category'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($volunteer['country'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (!empty($volunteer['processed'])): ?>
                                            <span class="badge badge-green">Processed</span>
                                        <?php else: ?>
                                            <span class="badge badge-orange">Unprocessed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(formatDate($volunteer['received_at'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="actions">
                                        <form method="post">
                                            <input type="hidden" name="action" value="toggle_volunteer_status" />
                                            <input type="hidden" name="index" value="<?= $index ?>" />
                                            <button type="submit"><?= empty($volunteer['processed']) ? 'Mark processed' : 'Mark new' ?></button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="action" value="delete_volunteer" />
                                            <input type="hidden" name="index" value="<?= $index ?>" />
                                            <button type="submit" class="delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php if (!empty($volunteer['subcategory']) || !empty($volunteer['language'])): ?>
                                    <tr>
                                        <td colspan="7" style="background:#f9fafb; font-size:.9rem; color:#4b5563;">
                                            <?= htmlspecialchars('Subcategory: ' . ($volunteer['subcategory'] ?? '—') . ' | Language: ' . ($volunteer['language'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
