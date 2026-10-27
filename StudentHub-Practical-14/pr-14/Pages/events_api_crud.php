<?php
require_once __DIR__ . '/../php/auth.php';
if (empty($_SESSION['user_id'])) { header('Location: login_secure.php?message=login_required'); exit; }
if (($_SESSION['role'] ?? '') !== 'admin') { http_response_code(403); header('Location: dashboard.php?message=forbidden'); exit; }
$csrf = csrf_token();
function api_page_e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events API CRUD - StudentHub</title><link rel="stylesheet" href="../css/style.css">
<style>
.api-wrap{max-width:1180px;margin:30px auto;padding:0 18px}.api-panel{background:#fff;border-radius:12px;padding:24px;margin-bottom:22px;box-shadow:0 4px 14px #00000012}.api-panel h2,.api-panel h3{color:#1b5e20;margin-top:0}.api-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}.api-form label{display:block;font-weight:600;margin-bottom:5px}.api-form input,.api-form select,.api-form textarea,.api-search input,.api-search select{width:100%;padding:10px;border:1px solid #cbd5cb;border-radius:6px;font:inherit;box-sizing:border-box}.api-form textarea{min-height:105px;resize:vertical}.api-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:16px}.api-btn{display:inline-block;border:0;border-radius:6px;padding:10px 16px;background:#1b5e20;color:#fff;text-decoration:none;font:inherit;cursor:pointer}.api-btn:disabled{opacity:.6;cursor:wait}.api-secondary{background:#455a64}.api-danger{background:#b3261e}.api-small{padding:6px 10px;font-size:.9rem}.api-notice{padding:12px 15px;border-radius:7px;margin-bottom:18px;display:none}.api-notice.success{display:block;background:#e8f5e9;color:#1b5e20}.api-notice.error{display:block;background:#ffebee;color:#b71c1c}.api-search{display:grid;grid-template-columns:minmax(200px,1fr) 200px auto auto;gap:10px;align-items:center}.api-table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:800px}th,td{text-align:left;padding:11px 10px;border-bottom:1px solid #e4e9e4;vertical-align:top}th{background:#f0f7f0;color:#204b24}.api-status{display:inline-block;padding:4px 8px;border-radius:20px;background:#edf5ed;font-size:.85rem;text-transform:capitalize}.api-muted{color:#607060}.api-loading{text-align:center;padding:22px;color:#607060}.api-row-actions{display:flex;gap:6px;flex-wrap:wrap}.api-description{max-width:320px;white-space:pre-wrap;overflow-wrap:anywhere}.api-status-line{font-size:.92rem;color:#607060;margin-top:8px}@media(max-width:650px){.api-panel{padding:16px}.api-search{grid-template-columns:1fr}}
</style>
</head><body>
<header><div class="logo-area"><img src="../images/logo.png" alt="StudentHub Logo"><div><h1>StudentHub</h1><p>Your Student Portal</p></div></div></header>
<nav aria-label="Main Navigation"><a href="index.html">Home</a><a href="dashboard.php">Dashboard</a><a href="admin_dashboard.php">Admin</a><a href="events.html">Events</a><a href="faq.html">FAQ</a></nav>
<main class="api-wrap">
<section class="api-panel"><h2>Event Management — JSON API + Fetch</h2><p>Create, list, edit, and delete events asynchronously. Requests use the PHP JSON API and update the table without a full page reload.</p><div id="apiNotice" class="api-notice" role="status" aria-live="polite"></div>
<h3 id="formHeading">Add New Event</h3><form id="eventForm" class="api-form">
<input type="hidden" id="eventId" value="">
<div><label for="title">Event title *</label><input id="title" name="title" maxlength="150" required></div>
<div><label for="category">Category *</label><select id="category" name="category" required><option value="">Select category</option><?php foreach (['Technical','Workshop','Sports','Cultural','Career','Other'] as $category): ?><option value="<?= api_page_e($category) ?>"><?= api_page_e($category) ?></option><?php endforeach; ?></select></div>
<div><label for="eventDate">Date and time *</label><input id="eventDate" name="event_date" type="datetime-local" required></div>
<div><label for="eventStatus">Status *</label><select id="eventStatus" name="status" required><option value="upcoming">Upcoming</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
<div style="grid-column:1/-1"><label for="description">Description *</label><textarea id="description" name="description" maxlength="5000" required></textarea></div>
<div class="api-actions" style="grid-column:1/-1"><button class="api-btn" id="saveButton" type="submit">Add Event</button><button class="api-btn api-secondary" id="cancelEdit" type="button" hidden>Cancel Edit</button></div>
</form><p class="api-status-line" id="requestState" aria-live="polite">Ready.</p></section>
<section class="api-panel"><h3>Search and Filter Events</h3><form id="searchForm" class="api-search"><input id="searchInput" type="search" placeholder="Search title, description, category" aria-label="Search events"><select id="statusFilter" aria-label="Filter by status"><option value="">All statuses</option><option value="upcoming">Upcoming</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select><button class="api-btn" type="submit">Search / Filter</button><button class="api-btn api-secondary" id="resetSearch" type="button">Reset</button></form></section>
<section class="api-panel"><h3>Event Records <span id="recordCount">(0)</span></h3><div class="api-table-scroll"><table><thead><tr><th>ID</th><th>Event</th><th>Category</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody id="eventRows"><tr><td colspan="6" class="api-loading">Loading events…</td></tr></tbody></table></div></section>
<form action="../php/logout.php" method="post" style="text-align:right;margin:0 0 24px"><input type="hidden" name="csrf_token" value="<?= api_page_e($csrf) ?>"><button class="api-btn api-secondary" type="submit">Logout</button></form>
</main><footer><p>&copy; 2026 StudentHub. All rights reserved.</p></footer>
<script>
(() => {
  'use strict';
  const API_URL = '../api/events.php';
  const CSRF_TOKEN = <?= json_encode($csrf) ?>;
  const form = document.getElementById('eventForm');
  const notice = document.getElementById('apiNotice');
  const rows = document.getElementById('eventRows');
  const count = document.getElementById('recordCount');
  const state = document.getElementById('requestState');
  const saveButton = document.getElementById('saveButton');
  let busy = false;

  function showNotice(message, type = 'success') {
    notice.textContent = message;
    notice.className = 'api-notice ' + (type === 'error' ? 'error' : 'success');
  }
  function clearNotice() { notice.textContent = ''; notice.className = 'api-notice'; }
  function setBusy(value, message = '') {
    busy = value;
    saveButton.disabled = value;
    state.textContent = message || (value ? 'Loading…' : 'Ready.');
  }
  async function request(url, options = {}) {
    const response = await fetch(url, options);
    let result;
    try { result = await response.json(); }
    catch (_) { throw new Error('The server returned an invalid JSON response. Check PHP errors and the API URL.'); }
    if (!response.ok || !result.success) throw new Error(result.message || 'The request failed.');
    return result;
  }
  function safeCell(text, className = '') {
    const td = document.createElement('td');
    if (className) td.className = className;
    td.textContent = text == null ? '' : String(text);
    return td;
  }
  function dateLabel(value) {
    const date = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleString(undefined, {day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'});
  }
  function actionButton(label, className, callback) {
    const button = document.createElement('button');
    button.type = 'button'; button.className = 'api-btn api-small ' + className; button.textContent = label;
    button.addEventListener('click', callback); return button;
  }
  function renderEvents(events) {
    rows.replaceChildren(); count.textContent = '(' + events.length + ')';
    if (!events.length) {
      const tr = document.createElement('tr'); const td = safeCell('No events found. Add an event or change your search filters.', 'api-loading'); td.colSpan = 6; tr.append(td); rows.append(tr); return;
    }
    events.forEach(event => {
      const tr = document.createElement('tr');
      tr.append(safeCell(event.id));
      const titleCell = document.createElement('td'); const strong = document.createElement('strong'); strong.textContent = event.title; titleCell.append(strong);
      const description = document.createElement('p'); description.className = 'api-description api-muted'; description.textContent = event.description; titleCell.append(description); tr.append(titleCell);
      tr.append(safeCell(event.category)); tr.append(safeCell(dateLabel(event.event_date)));
      const statusCell = document.createElement('td'); const pill = document.createElement('span'); pill.className = 'api-status'; pill.textContent = event.status; statusCell.append(pill); tr.append(statusCell);
      const actionCell = document.createElement('td'); const actions = document.createElement('div'); actions.className = 'api-row-actions';
      actions.append(actionButton('Edit', '', () => startEdit(event)));
      actions.append(actionButton('Delete', 'api-danger', () => deleteEvent(event)));
      actionCell.append(actions); tr.append(actionCell); rows.append(tr);
    });
  }
  async function loadEvents() {
    rows.innerHTML = '<tr><td colspan="6" class="api-loading">Loading events…</td></tr>';
    state.textContent = 'Loading events…';
    try {
      const params = new URLSearchParams();
      const search = document.getElementById('searchInput').value.trim();
      const status = document.getElementById('statusFilter').value;
      if (search) params.set('search', search);
      if (status) params.set('status', status);
      const result = await request(API_URL + (params.toString() ? '?' + params.toString() : ''), {headers: {'Accept':'application/json'}});
      renderEvents(result.data || []); state.textContent = 'Events loaded successfully.';
    } catch (error) {
      rows.innerHTML = '<tr><td colspan="6" class="api-loading">Unable to load events.</td></tr>';
      showNotice(error.message, 'error'); state.textContent = 'Request failed.';
    }
  }
  function startEdit(event) {
    document.getElementById('eventId').value = event.id;
    document.getElementById('title').value = event.title;
    document.getElementById('category').value = event.category;
    document.getElementById('description').value = event.description;
    document.getElementById('eventStatus').value = event.status;
    const date = new Date(String(event.event_date).replace(' ', 'T'));
    const pad = n => String(n).padStart(2, '0');
    if (!Number.isNaN(date.getTime())) document.getElementById('eventDate').value = `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    document.getElementById('formHeading').textContent = 'Edit Event #' + event.id;
    saveButton.textContent = 'Update Event'; document.getElementById('cancelEdit').hidden = false;
    clearNotice(); form.scrollIntoView({behavior:'smooth', block:'start'});
  }
  function resetForm() {
    form.reset(); document.getElementById('eventId').value = '';
    document.getElementById('formHeading').textContent = 'Add New Event'; saveButton.textContent = 'Add Event';
    document.getElementById('cancelEdit').hidden = true;
  }
  async function deleteEvent(event) {
    if (!window.confirm(`Delete “${event.title}” permanently? This cannot be undone.`)) return;
    clearNotice(); setBusy(true, 'Deleting event…');
    try {
      const result = await request(API_URL, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':CSRF_TOKEN}, body:JSON.stringify({action:'delete', id:Number(event.id)})});
      showNotice(result.message); if (String(document.getElementById('eventId').value) === String(event.id)) resetForm(); await loadEvents();
    } catch (error) { showNotice(error.message, 'error'); state.textContent = 'Delete failed.'; }
    finally { setBusy(false); }
  }
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (busy || !form.reportValidity()) return;
    clearNotice(); setBusy(true, 'Saving event…');
    const id = document.getElementById('eventId').value;
    const payload = {action:id ? 'update' : 'create', title:document.getElementById('title').value.trim(), category:document.getElementById('category').value, description:document.getElementById('description').value.trim(), event_date:document.getElementById('eventDate').value, status:document.getElementById('eventStatus').value};
    if (id) payload.id = Number(id);
    try {
      const result = await request(API_URL, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-Token':CSRF_TOKEN}, body:JSON.stringify(payload)});
      showNotice(result.message); resetForm(); await loadEvents();
    } catch (error) { showNotice(error.message, 'error'); state.textContent = 'Save failed.'; }
    finally { setBusy(false); }
  });
  document.getElementById('cancelEdit').addEventListener('click', resetForm);
  document.getElementById('searchForm').addEventListener('submit', event => { event.preventDefault(); clearNotice(); loadEvents(); });
  document.getElementById('resetSearch').addEventListener('click', () => { document.getElementById('searchInput').value=''; document.getElementById('statusFilter').value=''; clearNotice(); loadEvents(); });
  loadEvents();
})();
</script>
</body></html>
