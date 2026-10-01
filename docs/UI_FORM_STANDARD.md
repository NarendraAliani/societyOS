# SocietyOS UI Form Standard

## Modal-first forms

For SocietyOS, create and edit forms should use the shared Bootstrap modal pattern instead of remaining permanently visible inside the page content.

### Rules

- Use Bootstrap 5 modal markup with the shared `app-form-modal` class.
- Keep page content focused on lists, tables, KPIs, summaries, and actions.
- Put **Add/Create** forms behind a clear primary action button.
- Put **Edit** forms behind a small edit button on the corresponding row/card.
- Keep **Delete/Remove** as a separate explicit action with confirmation where appropriate.
- Use the standard modal structure:
  - `modal fade app-form-modal`
  - `modal-dialog modal-dialog-centered`
  - `modal-content`
  - `modal-header`
  - `modal-body`
  - `modal-footer`
- Use `\App\Helpers\Csrf::field()` in every POST form.
- Preserve server-side authorization/ownership checks for edit and delete actions.
- Prefer the existing route when the same endpoint can safely handle create/edit using an explicit record ID; otherwise add a dedicated update route.
- Keep modal forms responsive and compatible with SocietyOS Light, Mid, Dark, and font-size preferences.

### Shared styling

The reusable modal styling lives in:

`public/static/css/app.css`

Use the `app-form-modal` class for all new project forms that follow this standard.

### Current resident implementation

The resident **My Family**, **My Complaints**, and **Visitor Passes** pages follow this modal-first pattern. Family members and emergency contacts also expose Edit actions alongside Remove.
