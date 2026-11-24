# Architecture

## Request flow
Browser -> PHP page/controller -> shared bootstrap (session, PDO, CSRF, escaping) -> MySQL tables -> HTML response.

## Main entities
- Staff users authenticate to the administration interface.
- Patients are the central record.
- Doctors can be assigned to appointments and medical notes.
- Appointments connect a patient and doctor at a date/time.
- Medical notes store visit descriptions linked to patient/doctor and optionally an appointment.
- Invoices associate a patient with a charge and payment status.

## Trust boundaries
The demo uses fictional data. It is not designed or approved for production clinical use. Before production, add authorization per role and record, audit logs, encrypted backups, retention controls, secure deployment, privacy/legal review, and independent security testing.
