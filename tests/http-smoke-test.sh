#!/usr/bin/env bash
set -euo pipefail

BASE_URL="http://127.0.0.1:8080"
COOKIE_JAR="/tmp/caredesk-cookies.txt"
PAGE="/tmp/caredesk-page.html"
RESPONSE="/tmp/caredesk-response.html"

fail() { echo "FAIL: $1"; exit 1; }
pass() { echo "PASS: $1"; }

get_csrf() {
  local path="$1"
  curl -fsS -b "$COOKIE_JAR" -c "$COOKIE_JAR" "$BASE_URL/$path" -o "$PAGE" || fail "Could not load $path"
  local token
  token=$(grep -o 'name="csrf" value="[^"]*"' "$PAGE" | head -n1 | sed 's/.*value="//;s/"$//')
  [ -n "$token" ] || fail "Could not read CSRF token from $path"
  printf '%s' "$token"
}

post_expect_redirect() {
  local path="$1"
  shift
  local status
  status=$(curl -sS -o "$RESPONSE" -w '%{http_code}' -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST "$BASE_URL/$path" "$@")
  [ "$status" = "302" ] || fail "$path expected HTTP 302 after POST, got $status"
}

# Login page and CSRF token
curl -fsS -c "$COOKIE_JAR" "$BASE_URL/login.php" -o "$PAGE" || fail "Login page unavailable"
TOKEN=$(grep -o 'name="csrf" value="[^"]*"' "$PAGE" | head -n1 | sed 's/.*value="//;s/"$//')
[ -n "$TOKEN" ] || fail "Could not read login CSRF token"
pass "Login page and CSRF token"

# Invalid staff credentials must not create an authenticated session
STATUS=$(curl -sS -o "$RESPONSE" -w '%{http_code}' -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST "$BASE_URL/login.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "email=ci-admin@example.test" \
  --data-urlencode "password=wrong-password")
[ "$STATUS" = "200" ] || fail "Invalid login expected HTTP 200 with an error, got $STATUS"
grep -q "Invalid email or password" "$RESPONSE" || fail "Invalid login error message was not shown"
STATUS=$(curl -sS -o /dev/null -w '%{http_code}' -b "$COOKIE_JAR" "$BASE_URL/dashboard.php")
[ "$STATUS" = "302" ] || fail "Invalid login must not authenticate; dashboard expected HTTP 302, got $STATUS"
pass "Invalid staff credentials rejected without creating a session"

# Valid staff login
post_expect_redirect "login.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "email=ci-admin@example.test" \
  --data-urlencode "password=password"
pass "Staff login redirects to dashboard"

# Protected pages
for PAGE_NAME in dashboard.php patients.php doctors.php appointments.php medical-notes.php billing.php reports.php; do
  STATUS=$(curl -sS -o "$RESPONSE" -w '%{http_code}' -b "$COOKIE_JAR" "$BASE_URL/$PAGE_NAME")
  [ "$STATUS" = "200" ] || fail "$PAGE_NAME expected HTTP 200, got $STATUS"
  pass "$PAGE_NAME responds HTTP 200"
done

# Patient create and read
TOKEN=$(get_csrf "patients.php")
post_expect_redirect "patients.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "full_name=CI Smoke Patient" \
  --data-urlencode "phone=1111111111" \
  --data-urlencode "date_of_birth=1990-01-01" \
  --data-urlencode "gender=Prefer not to say"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/patients.php" -o "$RESPONSE"
grep -q "CI Smoke Patient" "$RESPONSE" || fail "Created patient was not rendered"
grep -q "CI Fictional Patient" "$RESPONSE" || fail "Seeded patient was not rendered"
pass "Patient create and list"

# Doctor create and read
TOKEN=$(get_csrf "doctors.php")
post_expect_redirect "doctors.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "full_name=CI Smoke Doctor" \
  --data-urlencode "specialty=General Medicine" \
  --data-urlencode "phone=2222222222"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/doctors.php" -o "$RESPONSE"
grep -q "CI Smoke Doctor" "$RESPONSE" || fail "Created doctor was not rendered"
pass "Doctor create and list"

# Appointment create and read. The CI seed inserts the first patient and doctor.
TOKEN=$(get_csrf "appointments.php")
post_expect_redirect "appointments.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "patient_id=1" \
  --data-urlencode "doctor_id=1" \
  --data-urlencode "appointment_date=2030-01-01T10:00" \
  --data-urlencode "reason=CI smoke appointment"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/appointments.php" -o "$RESPONSE"
grep -q "CI smoke appointment" "$RESPONSE" || fail "Created appointment was not rendered"
pass "Appointment create and list"

# Medical description create and read
TOKEN=$(get_csrf "medical-notes.php")
post_expect_redirect "medical-notes.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "patient_id=1" \
  --data-urlencode "doctor_id=1" \
  --data-urlencode "note_text=CI fictional visit note"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/medical-notes.php" -o "$RESPONSE"
grep -q "CI fictional visit note" "$RESPONSE" || fail "Created medical note was not rendered"
pass "Medical note create and list"

# Invoice create, mark paid, and report totals
TOKEN=$(get_csrf "billing.php")
post_expect_redirect "billing.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "patient_id=1" \
  --data-urlencode "description=CI smoke consultation" \
  --data-urlencode "amount=1250.50"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/billing.php" -o "$RESPONSE"
grep -q "CI smoke consultation" "$RESPONSE" || fail "Created invoice was not rendered"
grep -q "Pending" "$RESPONSE" || fail "New invoice should be pending"
TOKEN=$(grep -o 'name="csrf" value="[^"]*"' "$RESPONSE" | head -n1 | sed 's/.*value="//;s/"$//')
[ -n "$TOKEN" ] || fail "Could not read billing CSRF token"
post_expect_redirect "billing.php" \
  --data-urlencode "csrf=$TOKEN" \
  --data-urlencode "mark_paid=1"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/billing.php" -o "$RESPONSE"
grep -q "Paid" "$RESPONSE" || fail "Invoice did not change to paid"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/reports.php" -o "$RESPONSE"
grep -q "1,250.50" "$RESPONSE" || fail "Paid invoice total not reflected in reports"
pass "Invoice create, payment status update, and reporting"

# Invalid CSRF tokens must be rejected
STATUS=$(curl -sS -o "$RESPONSE" -w '%{http_code}' -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST "$BASE_URL/patients.php" \
  --data-urlencode "csrf=invalid-token" \
  --data-urlencode "full_name=Should Not Be Created")
[ "$STATUS" = "419" ] || fail "Invalid CSRF token expected HTTP 419, got $STATUS"
pass "Invalid CSRF token rejected"

# Logout and authorization check
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/logout.php" -o /dev/null
STATUS=$(curl -sS -o "$RESPONSE" -w '%{http_code}' "$BASE_URL/dashboard.php")
[ "$STATUS" = "302" ] || fail "Unauthenticated dashboard should redirect, got $STATUS"
pass "Logout and protected dashboard redirect"

echo "All HTTP integration smoke tests passed."
