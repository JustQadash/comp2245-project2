/**
 * COMP2245 Project 2 — Fix-It Desk
 * AJAX ticket filters, notes, status changes, and self-assignment.
 * PHP endpoints must use the JSON contract documented below.
 */
(() => {
  'use strict';

  const API = {
    tickets: 'api/tickets.php',
    notes: 'api/notes.php',
    update: 'api/ticket-update.php'
  };

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

  function showMessage(element, message, type = 'error') {
    if (!element) return;
    element.textContent = message;
    element.dataset.type = type;
    element.hidden = !message;
    element.setAttribute('role', type === 'error' ? 'alert' : 'status');
  }

  function csrfToken() {
    return $('meta[name="csrf-token"]')?.content || '';
  }

  async function requestJSON(url, options = {}) {
    const headers = { Accept: 'application/json', ...options.headers };
    const response = await fetch(url, {
      credentials: 'same-origin',
      ...options,
      headers
    });

    // An API must return JSON, including on authentication/CSRF errors.
    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      throw new Error('The server did not return JSON. Check the PHP endpoint.');
    }

    const data = await response.json();
    if (!response.ok || data.success === false) {
      if (response.status === 401) {
        throw new Error('Your session has expired. Please log in again.');
      }
      throw new Error(data.error || data.message || `Request failed (${response.status}).`);
    }
    return data;
  }

  function postJSON(url, payload) {
    const token = csrfToken();
    if (!token) return Promise.reject(new Error('Missing CSRF token on this page.'));

    return requestJSON(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': token
      },
      body: JSON.stringify({ ...payload, csrf_token: token })
    });
  }

  function addCell(row, text) {
    const cell = document.createElement('td');
    cell.textContent = text == null ? '' : String(text);
    row.appendChild(cell);
    return cell;
  }

  function renderTickets(tickets, tbody) {
    tbody.replaceChildren();

    if (tickets.length === 0) {
      const row = document.createElement('tr');
      const cell = document.createElement('td');
      cell.colSpan = 7;
      cell.textContent = 'No tickets match this filter.';
      row.appendChild(cell);
      tbody.appendChild(row);
      return;
    }

    for (const ticket of tickets) {
      const row = document.createElement('tr');
      addCell(row, ticket.id);

      const titleCell = document.createElement('td');
      const link = document.createElement('a');
      link.href = `ticket.php?id=${encodeURIComponent(ticket.id)}`;
      link.textContent = ticket.title;
      titleCell.appendChild(link);
      row.appendChild(titleCell);

      addCell(row, ticket.priority);
      addCell(row, ticket.status);
      addCell(row, ticket.created_by_name);
      addCell(row, ticket.assigned_to_name || 'Nobody');
      addCell(row, ticket.created_at);
      tbody.appendChild(row);
    }
  }

  function initTicketFilters() {
    const tbody = $('#tickets-body');
    const buttons = $$('[data-ticket-filter]');
    const count = $('#ticket-count');
    const message = $('#tickets-message');
    if (!tbody || buttons.length === 0) return;

    let loadSequence = 0;

    async function loadTickets(filter) {
      const sequence = ++loadSequence;
      buttons.forEach(button => { button.disabled = true; });
      showMessage(message, '');
      try {
        const url = `${API.tickets}?filter=${encodeURIComponent(filter)}`;
        const data = await requestJSON(url);
        if (sequence !== loadSequence) return;
        if (!Array.isArray(data.tickets)) {
          throw new Error('The ticket API did not return a tickets array.');
        }
        renderTickets(data.tickets, tbody);
        if (count) count.textContent = `${data.tickets.length} ticket${data.tickets.length === 1 ? '' : 's'}`;
        buttons.forEach(button => {
          const active = button.dataset.ticketFilter === filter;
          button.classList.toggle('active', active);
          button.classList.toggle('is-active', active);
          button.setAttribute('aria-pressed', String(active));
        });
      } catch (error) {
        if (sequence === loadSequence) showMessage(message, error.message);
      } finally {
        if (sequence === loadSequence) {
          buttons.forEach(button => { button.disabled = false; });
        }
      }
    }

    buttons.forEach(button => {
      button.addEventListener('click', () => {
        loadTickets(button.dataset.ticketFilter);
      });
    });

    // Fetch the initial list, without requiring a page refresh.
    loadTickets('all');
  }

  function makeNote(note) {
    const item = document.createElement('li');
    item.className = 'note-item';

    const heading = document.createElement('div');
    heading.className = 'note-meta';
    const author = document.createElement('strong');
    author.textContent = note.author_name;
    const date = document.createElement('small');
    date.textContent = ` · ${note.created_at}`;
    heading.append(author, date);

    const body = document.createElement('p');
    body.textContent = note.body;
    body.className = 'note-body';
    item.append(heading, body);
    return item;
  }

  function initAddNote(ticketId) {
    const form = $('#note-form');
    const notesList = $('#notes-list');
    if (!form || !notesList) return;

    const textarea = $('#note-body', form);
    const message = $('#note-message');
    const submit = form.querySelector('[type="submit"]');
    if (!textarea) return;

    form.addEventListener('submit', async event => {
      event.preventDefault();
      const body = textarea.value.trim();
      if (!body) {
        showMessage(message, 'Please write a note before submitting.');
        textarea.focus();
        return;
      }

      if (submit) submit.disabled = true;
      showMessage(message, '');
      try {
        const data = await postJSON(API.notes, { ticket_id: ticketId, body });
        if (!data.note) throw new Error('The server did not return the new note.');
        notesList.appendChild(makeNote(data.note));
        textarea.value = '';
        showMessage(message, 'Note added.', 'success');
      } catch (error) {
        showMessage(message, error.message);
      } finally {
        if (submit) submit.disabled = false;
      }
    });
  }

  function initStatusUpdate(ticketId) {
    const select = $('#ticket-status');
    if (!select) return;

    const statusText = $('#current-status');
    const updatedAt = $('#last-updated');
    const message = $('#status-message');
    let savedStatus = select.value;

    select.addEventListener('change', async () => {
      const requestedStatus = select.value;
      select.disabled = true;
      showMessage(message, '');
      try {
        const data = await postJSON(API.update, {
          ticket_id: ticketId,
          action: 'status',
          status: requestedStatus
        });
        if (!data.ticket) throw new Error('The server did not return the updated ticket.');
        const newStatus = data.ticket.status;
        savedStatus = newStatus;
        select.value = newStatus;
        if (statusText) {
          statusText.textContent = newStatus;
          statusText.classList.remove('badge-status-open', 'badge-status-in-progress', 'badge-status-closed', 'badge-status-progress');
          const badgeClasses = { 'Open': 'badge-status-open', 'In progress': 'badge-status-in-progress', 'Closed': 'badge-status-closed' };
          if (badgeClasses[newStatus]) statusText.classList.add(badgeClasses[newStatus]);
        }
        if (updatedAt) updatedAt.textContent = data.ticket.updated_at;
        showMessage(message, 'Status saved.', 'success');
      } catch (error) {
        select.value = savedStatus;
        showMessage(message, error.message);
      } finally {
        select.disabled = false;
      }
    });
  }

  function initAssignToMe(ticketId) {
    const button = $('#assign-to-me');
    if (!button) return;
    const assigneeText = $('#assigned-to-name');
    const updatedAt = $('#last-updated');
    const message = $('#assign-message');

    button.addEventListener('click', async () => {
      button.disabled = true;
      showMessage(message, '');
      try {
        const data = await postJSON(API.update, {
          ticket_id: ticketId,
          action: 'assign_to_me'
        });
        if (!data.ticket) throw new Error('The server did not return the updated ticket.');
        if (assigneeText) assigneeText.textContent = data.ticket.assigned_to_name;
        if (updatedAt) updatedAt.textContent = data.ticket.updated_at;
        button.hidden = true;
        showMessage(message, 'Ticket assigned to you.', 'success');
      } catch (error) {
        button.disabled = false;
        showMessage(message, error.message);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    initTicketFilters();
    const ticketPage = $('#ticket-page');
    if (!ticketPage) return;
    const ticketId = Number(ticketPage.dataset.ticketId);
    if (!Number.isSafeInteger(ticketId) || ticketId <= 0) return;
    initAddNote(ticketId);
    initStatusUpdate(ticketId);
    initAssignToMe(ticketId);
    const trigger = $('#delete-trigger-btn');
    const box = $('#delete-confirm-box');
    const cancel = $('#cancel-delete-btn');
    if (trigger && box) {
      trigger.addEventListener('click', () => { box.hidden = false; });
      if (cancel) cancel.addEventListener('click', () => { box.hidden = true; });
    }
  });
})();
