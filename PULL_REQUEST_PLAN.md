# Pull request plan

Each change is prepared on a dedicated branch and carries exactly one primary `type:` label. Dependencies are documented in the pull request description; unrelated changes are not bundled.

| Category | Label | Intended scope |
| --- | --- | --- |
| Timezone | `type: timezone` | Timezone handling, daylight-saving transitions, installation defaults, and time display. |
| Security fixes | `type: security` | Input validation, session and form protection, secure defaults, and fixes in affected modules. |
| Hooks | `type: hooks` | Hook dispatcher, documented extension points, bridges, and module integrations. |
| Admin theme | `type: admin-theme` | WBCE CMS admin theme, shared theme contracts, and backend interaction layout. |
| Language files | `type: i18n` | Language loading, German and English strings, and module localization. |
| Modular functions | `type: modularity` | Independently installable services, providers, bridges, and migration away from coupled code. |
| Asynchronous saving | `type: async` | Non-blocking backend saves, progress states, and asynchronous administrative actions. |
| Chunked upload | `type: chunked-upload` | Chunked/resumable upload endpoints, validation, recovery, and updater integration. |

## Preparation rule

1. Start each branch from the matching `upstream/1.7.0` commit.
2. Materialize only required core modules from `Module/` with `Shared/tools/apply_core_components.py`.
3. Keep each branch limited to its table row.
4. Run the relevant syntax and behaviour checks.
5. Open one pull request with the matching label and the repository template.
