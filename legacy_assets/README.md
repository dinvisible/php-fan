# Legacy Assets

This directory stores historical third-party assets that are intentionally kept
outside the web runtime. Files here are not loaded by Composer autoloading and
must not be exposed from `htdocs` without a dedicated migration review.

## `admin/fckeditor`

The bundled FCKeditor copy was removed from `htdocs/admin` because it is an
obsolete, unmaintained editor package. No first-party runtime code references it
directly. Keep it here only as a migration reference for projects that still need
to inspect old admin editor integrations.
