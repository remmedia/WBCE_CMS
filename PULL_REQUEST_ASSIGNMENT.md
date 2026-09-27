# Pull request assignment

This document assigns the current integration work to the eight review categories. Every pull request receives one primary label. Shared prerequisites are referenced as dependencies instead of being copied into several pull requests.

| Category | Primary components and paths | Dependencies |
| --- | --- | --- |
| Timezone | `framework/Timezone.php`, `framework/DateTime.php`, `admin/interface/timezones.php`, `admin/interface/date_formats.php`, `admin/interface/time_formats.php` | Language files |
| Security fixes | Secure form handling, session and login protection, input validation, CAPTCHA integration, Security Center fixes | Hooks where a module registers security checks |
| Hooks | `framework/HookDispatcher.php`, hook documentation, Hook Bridge, compatibility bridge integrations | Modular functions |
| Admin theme | `templates/wbce-cms/`, shared admin CSS and JavaScript, backend page layouts | Language files and asynchronous controls |
| Language files | `languages/`, `framework/i18n/`, localized backend and module strings, language-package installation | None |
| Modular functions | Authentication providers, two-factor providers, mailer providers, cookie providers, PHP and WBCE-1.6.8 bridges, worker-facing service interfaces | Hooks |
| Asynchronous saving | Backend AJAX save endpoints, settings and access administration, updater progress handling, worker dispatch integration | Admin theme and hooks |
| Chunked upload | `admin/addons/chunk_upload.php`, `admin/addons/chunk-upload.js`, language upload, package validation and updater upload integration | Security fixes and asynchronous saving |

## Branch order

1. `type: hooks` and `type: modularity` establish extension points and replaceable services.
2. `type: security`, `type: timezone`, and `type: i18n` supply independently reviewable functional corrections.
3. `type: async` and `type: chunked-upload` build on the validated service and security layers.
4. `type: admin-theme` contains the visual presentation of the accepted backend changes.

## Review rule

A pull request may touch supporting language strings and tests that are strictly necessary for its primary category. It must not include unrelated features, provider implementations, generated release files, local configuration, or package archives.

## Concrete changes

### `type: timezone`

- Central timezone initialization and consistent conversion between storage and display time.
- Correct handling of daylight-saving transitions.
- Consistent date, time, and timezone choices in the administrator interface.
- Main files: `framework/Timezone.php`, `framework/DateTime.php`, `admin/interface/timezones.php`, `admin/interface/date_formats.php`, `admin/interface/time_formats.php`.

### `type: security`

- Secure form and request validation improvements.
- Session, login, CAPTCHA, and authentication fallback hardening.
- Package and upload validation, including restricted server-function checks.
- Main files: `framework/SecureForm.php`, `framework/AuthenticationSession.php`, login and request handlers, `modules/SecureFormSwitcher/`, CAPTCHA integration points.

### `type: hooks`

- Central hook dispatcher and documented extension points.
- Hooks for authentication, backend actions, output filters, scheduled work, and security integrations.
- Compatibility adapters for legacy hook consumers.
- Main files: `framework/HookDispatcher.php`, `docs/HOOKS.md`, `modules/wbce_hook_bridge/`, hook calls in framework and module integrations.

### `type: admin-theme`

- New WBCE CMS administration theme and its shared theme contract.
- Responsive backend layouts, controls, notices, and admin-tool presentation.
- Main files: `templates/wbce-cms/`, shared administration CSS and JavaScript, backend templates.

### `type: i18n`

- Reliable language resolution with installed German module language files.
- Localized backend strings and module metadata.
- Installation and update handling for language packages.
- Main files: `framework/i18n/`, `languages/`, `admin/languages/`, module language directories.

### `type: modularity`

- Authentication and two-factor provider interfaces and provider-specific modules.
- Mailer, cookie-consent, CAPTCHA, compatibility, and worker services as independent modules.
- Separation between CMS integration and independently versioned extension sources.
- Main files: provider interfaces and registries, `Module/authentication/`, `Module/two_factor*/`, `Module/mailer*/`, `Module/php_compat_bridge/`, `Module/wbce_168_bridge/`.

### `type: async`

- Non-blocking saving in settings, access management, and administration tools.
- Progress feedback for long-running update and maintenance operations.
- Worker dispatch for deferred and scheduled tasks.
- Main files: backend AJAX endpoints, settings JavaScript, updater progress handlers, worker integration points.

### `type: chunked-upload`

- Resumable package and language uploads.
- Chunk assembly, upload-token validation, archive validation, and recovery after interrupted uploads.
- Updater integration for large update files.
- Main files: `admin/addons/chunk_upload.php`, `admin/addons/chunk-upload.js`, `admin/addons/UploadLanguage.php`, updater upload and archive-validation handlers.
