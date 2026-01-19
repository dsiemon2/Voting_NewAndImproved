# UI Components Architecture

This document describes the reusable UI components in the Voting Application that can be referenced or reused in other Laravel projects.

---

## 1. Sidebar Navigation Component

A reusable sidebar navigation component with scroll persistence, mobile responsiveness, and event context management.

### File Structure

```
public/
├── css/
│   └── sidebar.css              # All sidebar styles (BEM naming)
└── js/
    └── sidebar.js               # Sidebar JavaScript functionality

resources/views/
├── components/
│   └── sidebar.blade.php        # Blade component
└── layouts/
    └── app.blade.php            # Includes sidebar component
```

### Usage

**1. Include assets in layout:**
```blade
<!-- In <head> section -->
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

<!-- Before closing </body> -->
<script src="{{ asset('js/sidebar.js') }}"></script>
```

**2. Use the component:**
```blade
<div class="container">
    <x-sidebar />
    <main class="content">
        @yield('content')
    </main>
</div>
```

### Features

| Feature | Description |
|---------|-------------|
| **Scroll Persistence** | Saves sidebar scroll position to `sessionStorage`, restores on page load |
| **Event Context Menu** | Displays additional menu when managing a specific event |
| **Mobile Toggle** | Collapses to hamburger menu below 768px |
| **Cookie Management** | Tracks managed event via `managing_event_id` cookie |
| **Role-Based Menus** | Shows/hides admin-only sections based on user role |

### CSS Classes (BEM Naming)

```css
/* Base */
.sidebar                           /* Main sidebar container */

/* Menu Elements */
.sidebar__menu-list                /* ul element for menu items */
.sidebar__menu-header              /* Section header (Admin Menu, etc.) */
.sidebar__menu-header--spaced      /* Header with top margin */

/* Event Context */
.sidebar__event-menu               /* Event-specific menu wrapper */
.sidebar__event-selector-wrapper   /* Container for event name + close button */
.sidebar__event-selector           /* Event name link */
.sidebar__event-close              /* Close event context button */

/* Mobile */
.mobile-toggle                     /* Hamburger button (hidden on desktop) */
```

### JavaScript API

```javascript
// Object available globally
const Sidebar = {
    init(),                    // Initialize on DOM ready (automatic)
    toggle(),                  // Toggle mobile visibility
    initScrollPersistence(),   // Set up scroll memory
    initManagedEventCookie(),  // Sync window.managingEventId to cookie
    clearManagedEvent(),       // Clear event cookie and redirect
    setCookie(name, value, days),
    getCookie(name),
    deleteCookie(name)
};

// Global functions for HTML onclick
toggleSidebar()      // Calls Sidebar.toggle()
clearManagedEvent()  // Calls Sidebar.clearManagedEvent()
```

### Customization Points

| Variable | Location | Purpose |
|----------|----------|---------|
| `$isAdmin` | sidebar.blade.php | Check if user has Admin role |
| `$currentEvent` | sidebar.blade.php | Currently managed event object |
| Colors | sidebar.css | `#1e40af` (blue), `#ff6600` (orange active) |
| Width | sidebar.css | `250px` default |
| Height | sidebar.css | `calc(100vh - 70px)` accounts for header |

---

## 2. Pagination Component

Laravel's built-in pagination with consistent styling across all admin pages.

### Controller Pattern

```php
public function index()
{
    $items = Model::query()
        ->with(['relationship'])      // Eager load
        ->withCount('relatedItems')   // Count related
        ->orderBy('name')
        ->paginate(15);               // 15 items per page

    return view('admin.items.index', compact('items'));
}
```

### View Pattern

```blade
{{-- Table or card list --}}
@foreach($items as $item)
    ...
@endforeach

{{-- Pagination links --}}
@if($items->hasPages())
    <div class="pagination-wrapper" style="margin-top: 20px; text-align: center;">
        {{ $items->links() }}
    </div>
@endif
```

### Pages Using Pagination

| Page | Route | Items Per Page |
|------|-------|----------------|
| Events | `/admin/events` | 15 |
| Divisions | `/admin/events/{event}/divisions` | 15 |
| Participants | `/admin/events/{event}/participants` | 15 |
| Entries | `/admin/events/{event}/entries` | 15 |
| Categories | `/admin/events/{event}/categories` | 15 |
| Voting Types | `/admin/voting-types` | 15 |
| Webhooks | `/admin/webhooks` | 15 |
| Trial Codes | `/admin/trial-codes` | 15 |
| Users | `/admin/users` | 15 |

---

## 3. Responsive Admin Tables

Tables that convert to card layouts on mobile devices.

### HTML Structure

```blade
<table class="admin-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td data-label="Name">{{ $item->name }}</td>
                <td data-label="Email">{{ $item->email }}</td>
                <td data-label="Status">
                    <span class="badge badge-{{ $item->is_active ? 'success' : 'danger' }}">
                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td data-label="Actions">
                    <a href="{{ route('admin.items.edit', $item) }}" class="btn-edit">Edit</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

### CSS for Mobile Cards

```css
@media screen and (max-width: 768px) {
    .admin-table thead {
        display: none;
    }

    .admin-table tbody tr {
        display: block;
        margin-bottom: 15px;
        background: white;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .admin-table tbody td {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .admin-table tbody td:last-child {
        border-bottom: none;
    }

    .admin-table tbody td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #666;
    }
}
```

---

## 4. AI Chat Slider Component

A slide-in chat panel for AI assistant interactions.

### Files

| File | Purpose |
|------|---------|
| `resources/views/components/ai-chat-slider.blade.php` | Component view |
| `public/css/ai-chat.css` | Chat styles |
| `public/js/ai-chat.js` | Chat JavaScript |

### Features

- Slide-in from right side
- Voice input via Whisper API
- Message history
- Typing indicators
- Event context awareness

### Usage

```blade
<x-ai-chat-slider />
```

---

## 5. Badge Component

Status badges for displaying states.

### CSS Classes

```css
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.badge-success { background: #d4edda; color: #155724; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-danger  { background: #f8d7da; color: #721c24; }
.badge-info    { background: #d1ecf1; color: #0c5460; }
.badge-primary { background: #cce5ff; color: #004085; }
```

### Usage

```blade
<span class="badge badge-{{ $status }}">{{ ucfirst($status) }}</span>
```

---

## 6. Modal Component Pattern

Reusable modal structure.

### HTML Structure

```blade
<div class="modal" id="myModal" style="display: none;">
    <div class="modal-overlay" onclick="closeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Modal Title</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Content here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitForm()">Save</button>
        </div>
    </div>
</div>
```

### JavaScript

```javascript
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
    document.body.style.overflow = '';
}
```

---

## 7. Button Styles

Consistent button styling across the application.

### CSS

```css
.btn {
    display: inline-block;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.btn-primary   { background: #1e40af; color: white; }
.btn-primary:hover { background: #1e3a8a; }

.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }

.btn-success   { background: #059669; color: white; }
.btn-success:hover { background: #047857; }

.btn-danger    { background: #dc2626; color: white; }
.btn-danger:hover { background: #b91c1c; }

.btn-warning   { background: #d97706; color: white; }
.btn-warning:hover { background: #b45309; }
```

---

## Design Tokens

Consistent color palette used throughout the application.

| Token | Value | Usage |
|-------|-------|-------|
| `--color-primary` | `#1e40af` | Primary blue |
| `--color-primary-dark` | `#1e3a8a` | Darker blue (hover) |
| `--color-accent` | `#ff6600` | Orange accent (active states) |
| `--color-success` | `#059669` | Green success |
| `--color-danger` | `#dc2626` | Red danger/error |
| `--color-warning` | `#d97706` | Orange warning |
| `--color-text` | `#2c3e50` | Dark text |
| `--color-text-muted` | `#6b7280` | Muted text |
| `--color-background` | `#f3f4f6` | Page background |
| `--color-card` | `#ffffff` | Card/panel background |

---

## Best Practices

### 1. BEM Naming Convention
Use Block__Element--Modifier pattern for CSS classes:
```css
.sidebar { }                    /* Block */
.sidebar__menu-list { }         /* Element */
.sidebar__menu-header--spaced { } /* Modifier */
```

### 2. External Assets
Keep component CSS and JS in `public/` folder:
```
public/
├── css/
│   ├── sidebar.css
│   ├── ai-chat.css
│   └── admin.css
└── js/
    ├── sidebar.js
    ├── ai-chat.js
    └── admin.js
```

### 3. Blade Components
Use Laravel's component system:
```blade
{{-- Usage --}}
<x-sidebar />
<x-ai-chat-slider />
<x-badge type="success">Active</x-badge>

{{-- Component definition in resources/views/components/ --}}
```

### 4. Responsive Breakpoints
- **Desktop**: > 768px
- **Tablet**: 768px - 576px
- **Mobile**: < 576px

### 5. Accessibility
- Use `aria-label` for icon buttons
- Ensure sufficient color contrast
- Support keyboard navigation

---

*Document Version: 1.0*
*Created: January 19, 2026*
*Author: Claude Code Assistant*
