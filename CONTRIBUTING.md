# Contribution categories

Keep changes small enough to be reviewed and adopted independently. Each pull request has exactly one primary category and the matching GitHub label.

| Label | Category | Scope |
| --- | --- | --- |
| `type: timezone` | Timezone | Timezone calculation, daylight-saving time, and time display. |
| `type: security` | Security fixes | Security hardening, validation, access control, and secure defaults. |
| `type: hooks` | Hooks | New or changed hooks and hook integrations. |
| `type: admin-theme` | Layout in the admin theme | Presentation and interaction changes in admin themes. |
| `type: i18n` | Language files | Translations, language loading, and localization. |
| `type: modularity` | Modular functions | Moving or designing functions as isolated, replaceable modules. |
| `type: async` | Asynchronous saving | Non-blocking saving and asynchronous administrative actions. |
| `type: chunked-upload` | Chunked upload | Chunked and resumable uploads, including validation and recovery. |

If a change crosses categories, split it into separate commits and pull requests wherever the behaviour can be adopted independently.

The concrete branch and review scope for each category is maintained in [PULL_REQUEST_PLAN.md](PULL_REQUEST_PLAN.md).
