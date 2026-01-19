<?php
/**
 * ADMIN/NEWSLETTER-SUBSCRIBERS.PHP - Zarządzanie subskrybentami
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

$pageTitle = 'Subskrybenci Newsletter';
$currentPage = 'newsletter';

// Filtry
$search = $_GET['search'] ?? '';
$source = $_GET['source'] ?? '';
$status = $_GET['status'] ?? 'active';

// Paginacja
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Buduj zapytanie
$where = ["consent_marketing = 1"];
$params = [];

if ($search) {
    $where[] = "email LIKE ?";
    $params[] = "%$search%";
}

if ($source) {
    $where[] = "source = ?";
    $params[] = $source;
}

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

// Liczba wszystkich
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM marketing_consents WHERE $whereClause");
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $totalPages = ceil($total / $perPage);
} catch (Exception $e) {
    $total = 0;
    $totalPages = 0;
    error_log('Newsletter count error: ' . $e->getMessage());
}

// Pobierz subskrybentów
try {
    $stmt = $pdo->prepare("
        SELECT 
            email,
            source,
            status,
            subscribed_at,
            additional_data
        FROM marketing_consents
        WHERE $whereClause
        ORDER BY subscribed_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $subscribers = [];
    error_log('Newsletter subscribers error: ' . $e->getMessage());
}

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="subskrybenci_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Email', 'Źródło', 'Status', 'Data zapisu']);
    
    try {
        $stmt = $pdo->prepare("
            SELECT email, source, status, subscribed_at
            FROM marketing_consents
            WHERE $whereClause
            GROUP BY email
        ");
        $stmt->execute($params);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['email'],
                $row['source'],
                $row['status'],
                $row['subscribed_at']
            ]);
        }
    } catch (Exception $e) {
        // Error
    }
    
    fclose($output);
    exit;
}

include __DIR__ . '/includes/admin-header.php';
?>

<style>
.filters-bar {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.filters-form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    font-size: 14px;
}

.subscribers-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.stat-box {
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 16px;
    flex: 1;
}

.stat-box__value {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px 0;
}

.stat-box__label {
    font-size: 13px;
    color: #6B7280;
    margin: 0;
}

.table-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.pagination {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin-top: 20px;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #D1D5DB;
    border-radius: 6px;
    text-decoration: none;
    color: #374151;
    font-size: 14px;
}

.pagination a:hover {
    background: #F3F4F6;
}

.pagination .active {
    background: #2B59A6;
    color: white;
    border-color: #2B59A6;
}

.table-wrapper table {
    width: 100%;
    border-collapse: collapse;
}

.table-wrapper th {
    background: #F9FAFB;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    color: #6B7280;
    border-bottom: 1px solid #E5E7EB;
}

.table-wrapper td {
    padding: 12px 16px;
    border-bottom: 1px solid #F3F4F6;
    font-size: 14px;
    vertical-align: top;
}

.table-wrapper tbody tr:hover {
    background: #F9FAFB;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6B7280;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.3;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #374151;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.status-badge--sent {
    background: #D1FAE5;
    color: #065F46;
}

.status-badge--draft {
    background: #F3F4F6;
    color: #6B7280;
}
</style>

<div class="admin-content">
    <div class="page-header">
        <h1><i class="bi bi-people"></i> Subskrybenci Newsletter</h1>
        <a href="/admin/newsletter.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Powrót
        </a>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i>
        <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
    </div>
    <?php endif; ?>
    
    <!-- Statystyki -->
    <div class="subscribers-stats">
        <div class="stat-box">
            <p class="stat-box__value"><?php echo number_format($total, 0, ',', ' '); ?></p>
            <p class="stat-box__label">Znalezionych subskrybentów</p>
        </div>
    </div>
    
    <!-- Filtry -->
    <div class="filters-bar">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label>Szukaj po email</label>
                <input type="text" name="search" placeholder="np. jan@example.com" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="filter-group">
                <label>Źródło</label>
                <select name="source">
                    <option value="">Wszystkie</option>
                    <option value="newsletter" <?php echo $source === 'newsletter' ? 'selected' : ''; ?>>Newsletter (footer)</option>
                    <option value="contact" <?php echo $source === 'contact' ? 'selected' : ''; ?>>Formularz kontaktowy</option>
                    <option value="quote" <?php echo $source === 'quote' ? 'selected' : ''; ?>>Wycena / Kalkulator</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Aktywni</option>
                    <option value="unsubscribed" <?php echo $status === 'unsubscribed' ? 'selected' : ''; ?>>Wypisani</option>
                    <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>Wszyscy</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtruj
                </button>
            </div>
        </form>
    </div>
    
    <!-- Akcje -->
    <div class="table-actions">
        <div>
            <span style="color: #6B7280; font-size: 14px;">
                Znaleziono <strong><?php echo number_format($total, 0, ',', ' '); ?></strong> subskrybentów
            </span>
        </div>
        <div>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" class="btn btn-secondary">
                <i class="bi bi-download"></i> Eksportuj CSV
            </a>
        </div>
    </div>
    
    <!-- Tabela -->
    <div class="table-wrapper">
        <?php if (empty($subscribers)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h3>Brak subskrybentów</h3>
            <p>Nie znaleziono żadnych subskrybentów z wybranymi filtrami</p>
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Źródło</th>
                    <th>Status</th>
                    <th>Data zapisu</th>
                    <th>Dodatkowe info</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($sub['email']); ?></strong>
                    </td>
                    <td>
                        <?php
                        $sourceIcons = [
                            'newsletter' => '📧',
                            'contact' => '💬',
                            'quote' => '💰'
                        ];
                        $sourceLabels = [
                            'newsletter' => 'Newsletter',
                            'contact' => 'Kontakt',
                            'quote' => 'Wycena'
                        ];
                        $icon = $sourceIcons[$sub['source']] ?? '📋';
                        $label = $sourceLabels[$sub['source']] ?? $sub['source'];
                        echo $icon . ' ' . $label;
                        ?>
                    </td>
                    <td>
                        <?php if ($sub['status'] === 'active'): ?>
                        <span class="status-badge status-badge--sent">Aktywny</span>
                        <?php else: ?>
                        <span class="status-badge status-badge--draft">Wypisany</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size: 13px;">
                        <?php echo date('d.m.Y H:i', strtotime($sub['subscribed_at'])); ?>
                    </td>
                    <td style="font-size: 12px; color: #6B7280; max-width: 200px;">
                        <?php 
                        if ($sub['additional_data']) {
                            $data = json_decode($sub['additional_data'], true);
                            $parts = [];
                            if (isset($data['name'])) {
                                $parts[] = '👤 ' . htmlspecialchars($data['name']);
                            }
                            if (isset($data['phone'])) {
                                $parts[] = '📞 ' . htmlspecialchars($data['phone']);
                            }
                            echo $parts ? implode('<br>', $parts) : '-';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <!-- Paginacja -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
            <i class="bi bi-chevron-left"></i> Poprzednia
        </a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
            <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                <?php echo $i; ?>
            </a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
            Następna <i class="bi bi-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>