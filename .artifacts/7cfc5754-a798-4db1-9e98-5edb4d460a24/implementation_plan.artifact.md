# SaaS Multi-Tenant Evolution & UI Overhaul

This plan transforms the current starter kit into a professional SaaS platform where each business (BarberShop) has its own isolated data, custom branding, and feature controls (SMS Gateway). It also includes a major UI polish for the mobile settings.

## User Review Required

> [!IMPORTANT]
> - This change will make `barber_shop_id` a requirement for most data operations.
> - The mobile application will dynamically change its colors based on the logged-in user's business profile.
> - Existing data in `barber_shops` and `users` will need to be linked manually or via a seeder after the migration.

## Proposed Changes

### [Backend] Multi-Tenancy Core

#### [NEW] [BelongsToTenant.php](file:///C:/laragon/www/LaraFluterAuto/app/Traits/BelongsToTenant.php)
- Implement a Global Scope to filter all queries by `barber_shop_id`.
- Automatically set `barber_shop_id` on model creation.

#### [MODIFY] [BarberShop.php](file:///C:/laragon/www/LaraFluterAuto/app/Models/BarberShop.php)
- Add branding fields: `logo`, `primary_color`, `app_name`.
- Add SMS config: `sms_enabled` (boolean).
- Add subscription: `trial_ends_at`.

#### [MODIFY] [User.php](file:///C:/laragon/www/LaraFluterAuto/app/Models/User.php)
- Add `barber_shop_id` relationship.

#### [MODIFY] [AuthController.php](file:///C:/laragon/www/LaraFluterAuto/app/Http/Controllers/Api/Mobile/AuthController.php)
- Return `business_profile` (branding, settings) upon successful login.

---

### [Flutter] Dynamic Branding & UI Overhaul

#### [NEW] [branding_cubit.dart](file:///C:/laragon/www/LaraFluterAuto/mobile-gateway/lib/core/branding/branding_cubit.dart)
- Manage application branding state (colors, name, logo).
- Persist branding settings in `SharedPreferences`.

#### [MODIFY] [main.dart](file:///C:/laragon/www/LaraFluterAuto/mobile-gateway/lib/main.dart)
- Wrap `MaterialApp` with `BlocBuilder<BrandingCubit>` to enable dynamic theme switching.

#### [MODIFY] [settings_page.dart](file:///C:/laragon/www/LaraFluterAuto/mobile-gateway/lib/modules/settings/presentation/pages/settings_page.dart)
- **Compact Layout**: Move server metrics into a horizontal scrollable row or a more compact grid.
- **SMS Control**: Add a premium toggle for SMS Gateway.
- **Branding Preview**: Show the current business logo and primary color.
- **Trial Info**: Add a professional subscription status banner.

## Verification Plan

### Automated Tests
- `php artisan test` to ensure global scopes don't break existing logic.
- Verify API responses for `login` and `me` endpoints.

### Manual Verification
- Login with different business users and verify if the APK theme changes color.
- Create a "Booking" with one user and verify it's NOT visible to another user from a different shop.
- Toggle SMS Gateway in Settings and verify the database change.
