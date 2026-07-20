# UI Layouts and Tenant Switching

The product UI has two deliberate layout boundaries:

- `resources/views/layouts/auth.blade.php` is the central Arabic/RTL layout for login, registration, tenant selection, and invitation acceptance. It has no tenant sidebar or tenant switcher.
- `resources/views/layouts/app.blade.php` is the authenticated tenant Arabic/RTL shell. It provides the right sidebar, responsive mobile drawer, breadcrumbs, role-aware navigation, and the navbar tenant switcher.
- `Modules/Tracker` remains independent English/LTR and must not inherit either product layout.

## Tenant switcher

The navbar switcher is presentation only. It lists the active tenants returned by the authenticated user's `accessibleTenants()` relationship and submits the existing secure routes:

```text
GET  /tenant/select
POST /tenant/select/{tenant}
```

The POST endpoint revalidates the authenticated user, membership, tenant status, and soft-delete state before storing `current_tenant_id`. It clears the request-scoped `TenantContext` before redirecting to the intended URL or the tenant dashboard. The standalone selection page is the fallback when tenant middleware cannot establish a valid context.

The modal supports search, current-tenant highlighting, backdrop dismissal, Escape dismissal, focus return, and dialog ARIA attributes. It does not introduce a second switching workflow or trust tenant IDs from client input.

## Navigation authorization

`ProductNavigation` supplies links for implemented product surfaces. Dashboard and accessible branches are visible to active members; settings, branch management, and invitations are visible only to active owners and managers. This visibility improves usability but never replaces middleware, policies, Form Requests, or action-level authorization.

Every visible product link must resolve to a registered route, and every new product page must be reachable from the shared shell or a contextual action. Future areas are rendered as non-clickable “Coming soon” items until their routes and authorization are implemented.

