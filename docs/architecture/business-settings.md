# Business Settings

Business settings live in the `Modules/Business` module and are stored in the tenant-owned `business_settings` table. The model uses the Identity module's `BelongsToTenant` contract, so it cannot be queried or created without a trusted `TenantContext`.

## Configuration policy

- The editable `display_name` is separate from `tenants.name`; changing it never changes the tenant slug or identity.
- New settings default to `Africa/Cairo`, EGP, VAT enabled, VAT-inclusive pricing, and a 14% VAT rate.
- Currency is restricted to the configured MVP list: EGP, USD, EUR, GBP, SAR, and AED.
- Timezones are validated using PHP/IANA timezone identifiers.
- VAT mode is deliberately fixed to `inclusive` for the Egypt MVP; ETA credentials and Stripe settings do not belong here.

## Authorization and routes

`GET /tenant/settings/business` and PUT/PATCH `/tenant/settings/business` require `auth` and `tenant` middleware. Only active owner and manager memberships may view or update settings. Form Request authorization is enforced server-side and is independent of the UI.

## Historical values

`BusinessSettingsSnapshot` captures the business identity, timezone, currency, VAT policy, and receipt display defaults at a point in time. Future sale and receipt modules must persist this snapshot with their historical documents so later settings changes cannot rewrite financial history.

## Receipt numbers

`ReceiptNumberAllocator` locks the current tenant's settings row inside a database transaction, consumes `next_receipt_number`, and returns a prefix plus six-digit sequence such as `POS-000001`. The sequence is tenant-isolated and is designed for later POS receipt creation.
