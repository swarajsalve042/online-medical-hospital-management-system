#!/usr/bin/env bash
set -euo pipefail
BASE_URL="http://127.0.0.1:8080"
COOKIE_JAR="/tmp/caredesk-cookies.txt"
LOGIN_HTML="/tmp/caredesk-login.html"
RESPONSE="/tmp/caredesk-response.html"
fail() { echo "FAIL: $1"; exit 1; }
pass() { echo "PASS: $1"; }
curl -fsS -c "$COOKIE_JAR" "$BASE_URL/login.php" -o "$LOGIN_HTML" || fail "Login page unavailable"
TOKEN=$(grep -o 'name="csrf" value="[^"]*"' "$LOGIN_HTML" | head -n1 | sed 's/.*value="//;s/"$//')
[ -n "$TOKEN" ] || fail "Could not read login CSRF token"
pass "Login page and CSRF token"
STATUS=$(curl -sS -o "$RESPONSE" -w '%{http_code}' -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST "$BASE_URL/login.php" --data-urlencode "csrf=$TOKEN" --data-urlencode "email=ci-admin@example.test" --data-urlencode "password=password")
[ "$STATUS" = "302" ] || fail "Valid login expected HTTP 302, got $STATUS"
pass "Staff login"
for PAGE in dashboard.php patients.php doctors.php appointments.php medical-notes.php billing.php reports.php; do
  STATUS=$(curl -sS -o "$RESPONSE" -w '%{http_code}' -b "$COOKIE_JAR" "$BASE_URL/$PAGE")
  [ "$STATUS" = "200" ] || fail "$PAGE expected HTTP 200, got $STATUS"
  pass "$PAGE responds HTTP 200"
done
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/patients.php" -o "$RESPONSE"
grep -q "CI Fictional Patient" "$RESPONSE" || fail "Seeded patient not shown"
pass "Patient data rendered"
curl -fsS -b "$COOKIE_JAR" "$BASE_URL/logout.php" -o /dev/null
STATUS=$(curl -sS -o "$RESPONSE" -w '%{http_code}' "$BASE_URL/dashboard.php")
[ "$STATUS" = "302" ] || fail "Unauthenticated dashboard should redirect, got $STATUS"
pass "Protected dashboard redirects after logout"
echo "All HTTP smoke tests passed."
