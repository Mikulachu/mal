<?php
/**
 * ADMIN/NEWSLETTER-CREATE.PHP - Kreator zapisujący do bazy danych
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

$pageTitle = 'Nowa kampania';
$currentPage = 'newsletter';

// Obsługa zapisu
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $name = trim($_POST['name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $blocksJson = $_POST['blocks_json'] ?? '[]';
    
    if (empty($name)) $errors[] = 'Nazwa kampanii jest wymagana';
    if (empty($subject)) $errors[] = 'Temat wiadomości jest wymagany';
    
    $blocks = json_decode($blocksJson, true);
    if (!is_array($blocks) || empty($blocks)) {
        $errors[] = 'Dodaj przynajmniej jeden blok';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Utwórz kampanię
            $stmt = $pdo->prepare("
                INSERT INTO newsletter_campaigns 
                (name, subject, status, created_at, created_by)
                VALUES (?, ?, 'draft', NOW(), ?)
            ");
            $adminId = $_SESSION['admin_id'] ?? null;
            $stmt->execute([$name, $subject, $adminId]);
            $campaignId = $pdo->lastInsertId();
            
            // 2. Zapisz bloki do tabeli newsletter_blocks
            $stmt = $pdo->prepare("
                INSERT INTO newsletter_blocks 
                (campaign_id, block_type, block_order, content)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($blocks as $index => $block) {
                $blockType = $block['type'] ?? 'text';
                $blockContent = json_encode($block['content'] ?? []);
                $stmt->execute([$campaignId, $blockType, $index, $blockContent]);
            }
            
            $pdo->commit();
            
            $_SESSION['success_message'] = 'Kampania została utworzona';
            header('Location: /admin/newsletter.php');
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Błąd zapisu: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/admin-header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.newsletter-builder * { box-sizing: border-box; }
.newsletter-builder { display: grid; grid-template-columns: 280px 1fr 320px; gap: 20px; margin-top: 20px; }
.blocks-sidebar { background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; height: calc(100vh - 200px); overflow-y: auto; position: sticky; top: 20px; }
.blocks-sidebar h3 { margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #374151; }
.block-category { margin-bottom: 20px; }
.block-category__title { font-size: 11px; font-weight: 600; color: #6B7280; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px; }
.block-item { background: #F9FAFB; border: 2px dashed #D1D5DB; border-radius: 6px; padding: 10px; margin-bottom: 8px; cursor: move; transition: all 0.2s; text-align: center; }
.block-item:hover { background: #EFF6FF; border-color: #2B59A6; transform: translateY(-2px); }
.block-item__icon { font-size: 24px; margin-bottom: 4px; }
.block-item__name { font-size: 12px; font-weight: 600; color: #374151; }
.block-item__desc { font-size: 10px; color: #9CA3AF; margin-top: 2px; }
.canvas-area { background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; min-height: calc(100vh - 200px); }
.canvas-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB; }
.canvas-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
.canvas-actions { display: flex; gap: 8px; }
.canvas-content { background: #F9FAFB; border: 2px dashed #D1D5DB; border-radius: 8px; min-height: 400px; padding: 20px; }
.canvas-content.empty { display: flex; align-items: center; justify-content: center; flex-direction: column; color: #9CA3AF; }
.canvas-content.empty i { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }
.canvas-block { background: white; border: 2px solid #E5E7EB; border-radius: 6px; padding: 16px; margin-bottom: 12px; position: relative; cursor: pointer; transition: border-color 0.2s; }
.canvas-block:hover { border-color: #2B59A6; }
.canvas-block.selected { border-color: #2B59A6; box-shadow: 0 0 0 3px rgba(43, 89, 166, 0.1); }
.canvas-block__toolbar { position: absolute; top: 8px; right: 8px; display: none; gap: 4px; background: white; padding: 4px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.canvas-block:hover .canvas-block__toolbar { display: flex; }
.canvas-block__toolbar button { width: 28px; height: 28px; border: 1px solid #D1D5DB; background: white; border-radius: 4px; cursor: pointer; font-size: 12px; color: #6B7280; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
.canvas-block__toolbar button:hover { background: #F3F4F6; color: #374151; border-color: #9CA3AF; }
.canvas-block__content { user-select: none; }
.settings-sidebar { background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; height: calc(100vh - 200px); overflow-y: auto; position: sticky; top: 20px; }
.settings-sidebar h3 { margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #374151; }
.settings-group { margin-bottom: 16px; }
.settings-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.settings-group input, .settings-group select, .settings-group textarea { width: 100%; padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 13px; }
.settings-group textarea { min-height: 80px; resize: vertical; font-family: inherit; }
.color-picker-wrapper { display: flex; gap: 8px; }
.color-picker-wrapper input[type="color"] { width: 50px; height: 38px; border: 1px solid #D1D5DB; border-radius: 6px; cursor: pointer; }
.color-picker-wrapper input[type="text"] { flex: 1; }
.help-text { font-size: 11px; color: #6B7280; margin-top: 4px; }
.variables-hint { background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 6px; padding: 10px; margin-top: 12px; }
.variables-hint__title { font-size: 11px; font-weight: 600; color: #1E40AF; margin-bottom: 6px; }
.variable-tag { display: inline-block; background: #DBEAFE; color: #1E40AF; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-family: monospace; margin: 2px; cursor: pointer; transition: background 0.2s; }
.variable-tag:hover { background: #BFDBFE; }
.top-bar { background: white; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
.top-bar-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-field label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-field input { width: 100%; padding: 10px 14px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; }
.actions-bar { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
.preview-modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center; padding: 20px; }
.preview-modal.active { display: flex; }
.preview-modal__content { background: white; border-radius: 8px; max-width: 700px; width: 100%; max-height: 90vh; overflow-y: auto; position: relative; }
.preview-modal__header { padding: 20px; border-bottom: 1px solid #E5E7EB; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 1; }
.preview-modal__header h3 { margin: 0; font-size: 16px; }
.preview-modal__close { width: 32px; height: 32px; border: none; background: #F3F4F6; border-radius: 6px; cursor: pointer; font-size: 18px; color: #6B7280; }
.preview-modal__body { padding: 20px; }
.email-container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.email-body { padding: 40px 30px; }
.email-footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; font-size: 14px; }
</style>

<div class="admin-content">
    <div class="page-header">
        <h1><i class="bi bi-magic"></i> Kreator Newslettera</h1>
        <a href="/admin/newsletter.php" class="btn btn-secondary">
            <i class="bi bi-x"></i> Anuluj
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-x-circle"></i>
        <ul style="margin: 8px 0 0 20px; padding: 0;">
            <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="top-bar">
        <div class="top-bar-grid">
            <div class="form-field">
                <label>Nazwa kampanii <span style="color: #DC2626;">*</span></label>
                <input type="text" id="campaign-name" placeholder="np. Newsletter Styczeń 2026" required>
            </div>
            <div class="form-field">
                <label>Temat wiadomości <span style="color: #DC2626;">*</span></label>
                <input type="text" id="campaign-subject" placeholder="np. Promocja -30% na wszystkie usługi!" required>
            </div>
        </div>
    </div>
    
    <div class="newsletter-builder">
        <div class="blocks-sidebar">
            <h3><i class="bi bi-grid-3x3"></i> Dostępne bloki</h3>
            <div class="block-category">
                <div class="block-category__title">Treść</div>
                <div class="block-item" draggable="true" data-block-type="heading">
                    <div class="block-item__icon">📝</div>
                    <div class="block-item__name">Nagłówek</div>
                    <div class="block-item__desc">H1, H2, H3</div>
                </div>
                <div class="block-item" draggable="true" data-block-type="text">
                    <div class="block-item__icon">📄</div>
                    <div class="block-item__name">Tekst</div>
                    <div class="block-item__desc">Akapit</div>
                </div>
                <div class="block-item" draggable="true" data-block-type="image">
                    <div class="block-item__icon">🖼️</div>
                    <div class="block-item__name">Obrazek</div>
                    <div class="block-item__desc">Zdjęcie z linkiem</div>
                </div>
                <div class="block-item" draggable="true" data-block-type="divider">
                    <div class="block-item__icon">➖</div>
                    <div class="block-item__name">Separator</div>
                    <div class="block-item__desc">Linia</div>
                </div>
            </div>
            <div class="block-category">
                <div class="block-category__title">Akcje</div>
                <div class="block-item" draggable="true" data-block-type="button">
                    <div class="block-item__icon">🔘</div>
                    <div class="block-item__name">Przycisk</div>
                    <div class="block-item__desc">CTA button</div>
                </div>
                <div class="block-item" draggable="true" data-block-type="buttons-row">
                    <div class="block-item__icon">🔘🔘</div>
                    <div class="block-item__name">Rząd przycisków</div>
                    <div class="block-item__desc">2-3 przyciski</div>
                </div>
            </div>
            <div class="block-category">
                <div class="block-category__title">Zaawansowane</div>
                <div class="block-item" draggable="true" data-block-type="conditional">
                    <div class="block-item__icon">🔀</div>
                    <div class="block-item__name">Warunkowa treść</div>
                    <div class="block-item__desc">Jeśli/Inaczej</div>
                </div>
                <div class="block-item" draggable="true" data-block-type="social">
                    <div class="block-item__icon">📱</div>
                    <div class="block-item__name">Social Media</div>
                    <div class="block-item__desc">FB, IG, LinkedIn</div>
                </div>
            </div>
        </div>
        
        <div class="canvas-area">
            <div class="canvas-header">
                <h3>Twoja wiadomość</h3>
                <div class="canvas-actions">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="previewNewsletter()">
                        <i class="bi bi-eye"></i> Podgląd
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="clearCanvas()">
                        <i class="bi bi-trash"></i> Wyczyść
                    </button>
                </div>
            </div>
            <div class="canvas-content empty" id="canvas">
                <i class="bi bi-inbox"></i>
                <p>Przeciągnij bloki z lewej strony</p>
            </div>
        </div>
        
        <div class="settings-sidebar" id="settings-panel">
            <h3><i class="bi bi-sliders"></i> Ustawienia bloku</h3>
            <p style="color: #6B7280; font-size: 13px;">Kliknij na blok aby edytować</p>
            <div class="variables-hint">
                <div class="variables-hint__title">💡 Zmienne (kliknij aby wstawić)</div>
                <span class="variable-tag" onclick="insertVariable('{{first_name}}')">Imię</span>
                <span class="variable-tag" onclick="insertVariable('{{email}}')">Email</span>
                <span class="variable-tag" onclick="insertVariable('{{company_name}}')">Firma</span>
                <span class="variable-tag" onclick="insertVariable('{{company_phone}}')">Telefon</span>
            </div>
        </div>
    </div>
    
    <div class="actions-bar">
        <a href="/admin/newsletter.php" class="btn btn-secondary">
            <i class="bi bi-x"></i> Anuluj
        </a>
        <button type="button" class="btn btn-primary" onclick="saveNewsletter()">
            <i class="bi bi-save"></i> Zapisz jako szkic
        </button>
    </div>
</div>

<div class="preview-modal" id="preview-modal">
    <div class="preview-modal__content">
        <div class="preview-modal__header">
            <h3><i class="bi bi-eye"></i> Podgląd newslettera</h3>
            <button class="preview-modal__close" onclick="closePreview()">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <div class="preview-modal__body" style="background: #f4f4f4; padding: 40px 20px;">
            <div class="email-container" id="preview-content"></div>
        </div>
    </div>
</div>

<script>
let blocks = [];
let selectedBlock = null;
let blockIdCounter = 1;

function getDefaultContent(type) {
    const defaults = {
        'heading': {text: 'Witaj {{first_name}}!', size: 'h1', color: '#2B59A6', align: 'left'},
        'text': {text: 'To jest przykładowa treść newslettera.', color: '#374151', align: 'left'},
        'button': {text: 'Kliknij tutaj', link: 'https://maltechnik.pl', bg_color: '#2B59A6', text_color: '#FFFFFF', align: 'center'},
        'image': {url: '', alt: 'Obrazek', link: '', width: '100%'},
        'conditional': {condition: 'has_name', if_content: 'Witaj {{first_name}}!', else_content: 'Witaj!'},
        'divider': {color: '#E5E7EB', height: '2px'},
        'buttons-row': {buttons: [{text: 'Przycisk 1', link: '#', bg_color: '#2B59A6'}, {text: 'Przycisk 2', link: '#', bg_color: '#10B981'}]},
        'social': {facebook: 'https://facebook.com', instagram: 'https://instagram.com', linkedin: 'https://linkedin.com'}
    };
    return defaults[type] || {};
}

function addBlock(type) {
    const block = {
        id: blockIdCounter++,
        type: type,
        content: getDefaultContent(type)
    };
    blocks.push(block);
    renderCanvas();
}

function renderCanvas() {
    const canvas = document.getElementById('canvas');
    if (!canvas) return;
    
    if (blocks.length === 0) {
        canvas.innerHTML = '<i class="bi bi-inbox"></i><p>Przeciągnij bloki z lewej strony</p>';
        canvas.classList.add('empty');
        return;
    }
    
    canvas.classList.remove('empty');
    canvas.innerHTML = blocks.map(block => `
        <div class="canvas-block ${selectedBlock && selectedBlock.id === block.id ? 'selected' : ''}" 
             data-block-id="${block.id}" 
             onclick="selectBlock(${block.id})">
            <div class="canvas-block__toolbar">
                <button onclick="event.stopPropagation(); moveBlockUp(${block.id})" title="W górę">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button onclick="event.stopPropagation(); moveBlockDown(${block.id})" title="W dół">
                    <i class="bi bi-arrow-down"></i>
                </button>
                <button onclick="event.stopPropagation(); duplicateBlock(${block.id})" title="Duplikuj">
                    <i class="bi bi-files"></i>
                </button>
                <button onclick="event.stopPropagation(); deleteBlock(${block.id})" title="Usuń">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="canvas-block__content">
                ${renderBlockPreview(block)}
            </div>
        </div>
    `).join('');
}

function renderBlockPreview(block) {
    const renderers = {
        'heading': (b) => `<${b.content.size} style="color: ${b.content.color}; text-align: ${b.content.align}; margin: 0;">${b.content.text}</${b.content.size}>`,
        'text': (b) => `<p style="color: ${b.content.color}; text-align: ${b.content.align}; margin: 0;">${b.content.text}</p>`,
        'button': (b) => `<div style="text-align: ${b.content.align};"><span style="display: inline-block; background: ${b.content.bg_color}; color: ${b.content.text_color}; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: not-allowed;">${b.content.text}</span></div>`,
        'image': (b) => b.content.url ? `<img src="${b.content.url}" alt="${b.content.alt}" style="max-width: ${b.content.width}; height: auto; display: block; margin: 0;">` : '<div style="background: #F3F4F6; padding: 40px; text-align: center; color: #9CA3AF; border: 2px dashed #D1D5DB; border-radius: 6px;">Dodaj URL obrazka →</div>',
        'conditional': (b) => `<div style="background: #FEF3C7; padding: 12px; border-left: 3px solid #F59E0B; font-size: 13px;"><strong>🔀 Warunek:</strong> ${b.content.condition}<br><small style="color: #92400E;">JEŚLI:</small> ${b.content.if_content}<br><small style="color: #92400E;">INACZEJ:</small> ${b.content.else_content}</div>`,
        'divider': (b) => `<hr style="border: none; border-top: ${b.content.height} solid ${b.content.color}; margin: 16px 0;">`,
        'buttons-row': (b) => `<div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">${b.content.buttons.map(btn => `<span style="flex: 1; min-width: 120px; background: ${btn.bg_color}; color: white; padding: 12px; text-align: center; border-radius: 6px; cursor: not-allowed;">${btn.text}</span>`).join('')}</div>`,
        'social': (b) => {
            const links = [];
            if (b.content.facebook) links.push(`<span style="display: inline-block; margin: 0 8px; color: #1877F2; font-size: 24px; cursor: not-allowed;"><i class="bi bi-facebook"></i></span>`);
            if (b.content.instagram) links.push(`<span style="display: inline-block; margin: 0 8px; color: #E4405F; font-size: 24px; cursor: not-allowed;"><i class="bi bi-instagram"></i></span>`);
            if (b.content.linkedin) links.push(`<span style="display: inline-block; margin: 0 8px; color: #0A66C2; font-size: 24px; cursor: not-allowed;"><i class="bi bi-linkedin"></i></span>`);
            return `<div style="text-align: center;">${links.join('')}</div>`;
        }
    };
    return renderers[block.type] ? renderers[block.type](block) : '';
}

function selectBlock(id) {
    selectedBlock = blocks.find(b => b.id === id);
    renderCanvas();
    if (selectedBlock) showSettings(selectedBlock);
}

function showSettings(block) {
    const panel = document.getElementById('settings-panel');
    
    const forms = {
        'heading': `
            <div class="settings-group">
                <label>Tekst nagłówka</label>
                <input type="text" value="${block.content.text}" oninput="updateBlock('text', this.value)">
                <div class="help-text">💡 Użyj zmiennych: {{first_name}}, {{company_name}}</div>
            </div>
            <div class="settings-group">
                <label>Rozmiar</label>
                <select onchange="updateBlock('size', this.value)">
                    <option value="h1" ${block.content.size === 'h1' ? 'selected' : ''}>H1 - Bardzo duży</option>
                    <option value="h2" ${block.content.size === 'h2' ? 'selected' : ''}>H2 - Duży</option>
                    <option value="h3" ${block.content.size === 'h3' ? 'selected' : ''}>H3 - Średni</option>
                </select>
            </div>
            <div class="settings-group">
                <label>Kolor tekstu</label>
                <div class="color-picker-wrapper">
                    <input type="color" value="${block.content.color}" onchange="updateBlock('color', this.value); this.nextElementSibling.value = this.value;">
                    <input type="text" value="${block.content.color}" oninput="updateBlock('color', this.value); this.previousElementSibling.value = this.value;">
                </div>
            </div>
            <div class="settings-group">
                <label>Wyrównanie</label>
                <select onchange="updateBlock('align', this.value)">
                    <option value="left" ${block.content.align === 'left' ? 'selected' : ''}>Do lewej</option>
                    <option value="center" ${block.content.align === 'center' ? 'selected' : ''}>Do środka</option>
                    <option value="right" ${block.content.align === 'right' ? 'selected' : ''}>Do prawej</option>
                </select>
            </div>
        `,
        'text': `
            <div class="settings-group">
                <label>Treść</label>
                <textarea oninput="updateBlock('text', this.value)">${block.content.text}</textarea>
                <div class="help-text">💡 Użyj zmiennych: {{first_name}}, {{email}}</div>
            </div>
            <div class="settings-group">
                <label>Kolor tekstu</label>
                <div class="color-picker-wrapper">
                    <input type="color" value="${block.content.color}" onchange="updateBlock('color', this.value); this.nextElementSibling.value = this.value;">
                    <input type="text" value="${block.content.color}" oninput="updateBlock('color', this.value); this.previousElementSibling.value = this.value;">
                </div>
            </div>
        `,
        'button': `
            <div class="settings-group">
                <label>Tekst przycisku</label>
                <input type="text" value="${block.content.text}" oninput="updateBlock('text', this.value)">
            </div>
            <div class="settings-group">
                <label>Link (URL)</label>
                <input type="url" value="${block.content.link}" oninput="updateBlock('link', this.value)">
            </div>
            <div class="settings-group">
                <label>Kolor tła</label>
                <div class="color-picker-wrapper">
                    <input type="color" value="${block.content.bg_color}" onchange="updateBlock('bg_color', this.value); this.nextElementSibling.value = this.value;">
                    <input type="text" value="${block.content.bg_color}" oninput="updateBlock('bg_color', this.value); this.previousElementSibling.value = this.value;">
                </div>
            </div>
            <div class="settings-group">
                <label>Kolor tekstu</label>
                <div class="color-picker-wrapper">
                    <input type="color" value="${block.content.text_color}" onchange="updateBlock('text_color', this.value); this.nextElementSibling.value = this.value;">
                    <input type="text" value="${block.content.text_color}" oninput="updateBlock('text_color', this.value); this.previousElementSibling.value = this.value;">
                </div>
            </div>
        `,
        'image': `
            <div class="settings-group">
                <label>URL obrazka</label>
                <input type="url" value="${block.content.url}" oninput="updateBlock('url', this.value)" placeholder="https://example.com/image.jpg">
            </div>
            <div class="settings-group">
                <label>Tekst alternatywny</label>
                <input type="text" value="${block.content.alt}" oninput="updateBlock('alt', this.value)">
            </div>
            <div class="settings-group">
                <label>Link (opcjonalnie)</label>
                <input type="url" value="${block.content.link}" oninput="updateBlock('link', this.value)" placeholder="https://...">
            </div>
            <div class="settings-group">
                <label>Szerokość</label>
                <select onchange="updateBlock('width', this.value)">
                    <option value="100%" ${block.content.width === '100%' ? 'selected' : ''}>100% (pełna)</option>
                    <option value="80%" ${block.content.width === '80%' ? 'selected' : ''}>80%</option>
                    <option value="60%" ${block.content.width === '60%' ? 'selected' : ''}>60%</option>
                    <option value="40%" ${block.content.width === '40%' ? 'selected' : ''}>40%</option>
                </select>
            </div>
        `,
        'conditional': `
            <div class="settings-group">
                <label>Warunek</label>
                <select onchange="updateBlock('condition', this.value)">
                    <option value="has_name" ${block.content.condition === 'has_name' ? 'selected' : ''}>Odbiorca ma imię</option>
                    <option value="has_phone" ${block.content.condition === 'has_phone' ? 'selected' : ''}>Odbiorca ma telefon</option>
                    <option value="source_newsletter" ${block.content.condition === 'source_newsletter' ? 'selected' : ''}>Źródło: Newsletter</option>
                    <option value="source_contact" ${block.content.condition === 'source_contact' ? 'selected' : ''}>Źródło: Kontakt</option>
                    <option value="source_quote" ${block.content.condition === 'source_quote' ? 'selected' : ''}>Źródło: Wycena</option>
                </select>
            </div>
            <div class="settings-group">
                <label>Treść JEŚLI spełniony</label>
                <textarea oninput="updateBlock('if_content', this.value)">${block.content.if_content}</textarea>
            </div>
            <div class="settings-group">
                <label>Treść INACZEJ</label>
                <textarea oninput="updateBlock('else_content', this.value)">${block.content.else_content}</textarea>
            </div>
        `,
        'divider': `
            <div class="settings-group">
                <label>Kolor linii</label>
                <div class="color-picker-wrapper">
                    <input type="color" value="${block.content.color}" onchange="updateBlock('color', this.value); this.nextElementSibling.value = this.value;">
                    <input type="text" value="${block.content.color}" oninput="updateBlock('color', this.value); this.previousElementSibling.value = this.value;">
                </div>
            </div>
            <div class="settings-group">
                <label>Grubość</label>
                <select onchange="updateBlock('height', this.value)">
                    <option value="1px" ${block.content.height === '1px' ? 'selected' : ''}>Cienka (1px)</option>
                    <option value="2px" ${block.content.height === '2px' ? 'selected' : ''}>Średnia (2px)</option>
                    <option value="3px" ${block.content.height === '3px' ? 'selected' : ''}>Gruba (3px)</option>
                </select>
            </div>
        `,
        'social': `
            <div class="settings-group">
                <label>Facebook URL</label>
                <input type="url" value="${block.content.facebook}" oninput="updateBlock('facebook', this.value)" placeholder="https://facebook.com/twoja-strona">
            </div>
            <div class="settings-group">
                <label>Instagram URL</label>
                <input type="url" value="${block.content.instagram}" oninput="updateBlock('instagram', this.value)" placeholder="https://instagram.com/twoj-profil">
            </div>
            <div class="settings-group">
                <label>LinkedIn URL</label>
                <input type="url" value="${block.content.linkedin}" oninput="updateBlock('linkedin', this.value)" placeholder="https://linkedin.com/company/twoja-firma">
            </div>
        `
    };
    
    panel.innerHTML = `
        <h3><i class="bi bi-sliders"></i> Ustawienia bloku</h3>
        ${forms[block.type] || '<p>Brak ustawień</p>'}
        <div class="variables-hint">
            <div class="variables-hint__title">💡 Zmienne (kliknij aby wstawić)</div>
            <span class="variable-tag" onclick="insertVariable('{{first_name}}')">Imię</span>
            <span class="variable-tag" onclick="insertVariable('{{email}}')">Email</span>
            <span class="variable-tag" onclick="insertVariable('{{company_name}}')">Firma</span>
            <span class="variable-tag" onclick="insertVariable('{{company_phone}}')">Telefon</span>
        </div>
    `;
}

function updateBlock(key, value) {
    if (!selectedBlock) return;
    selectedBlock.content[key] = value;
    renderCanvas();
}

function moveBlockUp(id) {
    const index = blocks.findIndex(b => b.id === id);
    if (index > 0) {
        [blocks[index], blocks[index - 1]] = [blocks[index - 1], blocks[index]];
        renderCanvas();
    }
}

function moveBlockDown(id) {
    const index = blocks.findIndex(b => b.id === id);
    if (index < blocks.length - 1) {
        [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
        renderCanvas();
    }
}

function duplicateBlock(id) {
    const block = blocks.find(b => b.id === id);
    if (block) {
        const newBlock = {
            ...block,
            id: blockIdCounter++,
            content: {...block.content}
        };
        const index = blocks.findIndex(b => b.id === id);
        blocks.splice(index + 1, 0, newBlock);
        renderCanvas();
    }
}

function deleteBlock(id) {
    if (confirm('Usunąć ten blok?')) {
        blocks = blocks.filter(b => b.id !== id);
        if (selectedBlock && selectedBlock.id === id) {
            selectedBlock = null;
        }
        renderCanvas();
    }
}

function clearCanvas() {
    if (confirm('Wyczyścić całą zawartość?')) {
        blocks = [];
        selectedBlock = null;
        renderCanvas();
    }
}

function insertVariable(variable) {
    if (!selectedBlock) {
        alert('Wybierz najpierw blok tekstowy');
        return;
    }
    
    if (selectedBlock.content.text !== undefined) {
        selectedBlock.content.text += ' ' + variable;
        renderCanvas();
        showSettings(selectedBlock);
    }
}

function renderBlockFinal(block) {
    const renderers = {
        'heading': (b) => `<${b.content.size} style="color: ${b.content.color}; text-align: ${b.content.align}; margin: 0 0 16px 0;">${b.content.text}</${b.content.size}>`,
        'text': (b) => `<p style="color: ${b.content.color}; text-align: ${b.content.align}; margin: 0 0 16px 0; line-height: 1.6;">${b.content.text}</p>`,
        'button': (b) => `<div style="text-align: ${b.content.align}; margin: 20px 0;"><a href="${b.content.link}" style="display: inline-block; background: ${b.content.bg_color}; color: ${b.content.text_color}; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: 600;">${b.content.text}</a></div>`,
        'image': (b) => {
            const img = `<img src="${b.content.url}" alt="${b.content.alt}" style="max-width: ${b.content.width}; height: auto; display: block; margin: 16px auto; border-radius: 8px;">`;
            return b.content.link ? `<a href="${b.content.link}" style="display: block; text-align: center;">${img}</a>` : `<div style="text-align: center;">${img}</div>`;
        },
        'conditional': (b) => `<div>{{#if_${b.content.condition}}}${b.content.if_content}{{else}}${b.content.else_content}{{/if}}</div>`,
        'divider': (b) => `<hr style="border: none; border-top: ${b.content.height} solid ${b.content.color}; margin: 24px 0;">`,
        'buttons-row': (b) => `<div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin: 20px 0;">${b.content.buttons.map(btn => `<a href="${btn.link}" style="flex: 1; min-width: 140px; background: ${btn.bg_color}; color: white; padding: 14px 20px; text-align: center; text-decoration: none; border-radius: 6px; font-weight: 600;">${btn.text}</a>`).join('')}</div>`,
        'social': (b) => {
            const links = [];
            if (b.content.facebook) links.push(`<a href="${b.content.facebook}" style="display: inline-block; margin: 0 12px;"><img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" width="32" height="32" alt="Facebook"></a>`);
            if (b.content.instagram) links.push(`<a href="${b.content.instagram}" style="display: inline-block; margin: 0 12px;"><img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" width="32" height="32" alt="Instagram"></a>`);
            if (b.content.linkedin) links.push(`<a href="${b.content.linkedin}" style="display: inline-block; margin: 0 12px;"><img src="https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.png" width="32" height="32" alt="LinkedIn"></a>`);
            return `<div style="text-align: center; margin: 24px 0;">${links.join('')}</div>`;
        }
    };
    return renderers[block.type] ? renderers[block.type](block) : '';
}

function previewNewsletter() {
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');
    
    const html = `
        <div class="email-body">
            ${blocks.map(renderBlockFinal).join('')}
        </div>
        <div class="email-footer">
            <p style="margin: 0 0 10px 0; font-weight: 600;">Maltechnik</p>
            <p style="margin: 0 0 10px 0;">📞 +48 784 607 452 | ✉️ maltechnik.chojnice@gmail.com</p>
            <p style="margin: 0; font-size: 12px;">
                <a href="#" style="color: #6c757d; text-decoration: underline;">Wypisz się z newslettera</a>
            </p>
        </div>
    `;
    
    content.innerHTML = html;
    modal.classList.add('active');
}

function closePreview() {
    document.getElementById('preview-modal').classList.remove('active');
}

document.getElementById('preview-modal').addEventListener('click', function(e) {
    if (e.target === this) closePreview();
});

function saveNewsletter() {
    const name = document.getElementById('campaign-name').value.trim();
    const subject = document.getElementById('campaign-subject').value.trim();
    
    if (!name) {
        alert('Podaj nazwę kampanii');
        return;
    }
    
    if (!subject) {
        alert('Podaj temat wiadomości');
        return;
    }
    
    if (blocks.length === 0) {
        alert('Dodaj przynajmniej jeden blok');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="name" value="${name.replace(/"/g, '&quot;')}">
        <input type="hidden" name="subject" value="${subject.replace(/"/g, '&quot;')}">
        <input type="hidden" name="blocks_json" value='${JSON.stringify(blocks).replace(/'/g, "\\'")}'>
    `;
    document.body.appendChild(form);
    form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.block-item').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('blockType', this.dataset.blockType);
        });
    });
    
    const canvas = document.getElementById('canvas');
    canvas.addEventListener('dragover', (e) => e.preventDefault());
    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        const blockType = e.dataTransfer.getData('blockType');
        addBlock(blockType);
    });
});
</script>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>