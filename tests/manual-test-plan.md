# Manual test plan

Run in a local PHP 8.1+ and MySQL environment after importing database/schema.sql and configuring config/database.php.

1. Open setup.php once; confirm it creates a demo staff login only when staff_users is empty.
2. Delete setup.php after creating the local demo account.
3. Sign in with the generated credentials; verify dashboard loads.
4. Submit an empty patient form; verify validation message.
5. Add a fictional patient; verify it appears in the patient list.
6. Add a fictional doctor; verify it appears in the directory.
7. Create an appointment for the patient and doctor; verify it appears on dashboard and appointment list.
8. Create an invoice with a positive amount; verify it appears in billing and pending totals.
9. Verify logout returns to login and protected pages redirect to login.
10. Try a stale/invalid CSRF token; verify the write request is rejected.

Record actual results, environment versions, and screenshots here only after performing the tests. No runtime test result is asserted by this checklist.
