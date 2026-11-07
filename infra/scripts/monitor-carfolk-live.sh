#!/usr/bin/env bash
# Public-only read checks; no credentials, customer information or upload operations.
set -Eeuo pipefail

origin='https://carfolkltd.com'
issue_title='[Carfolk Monitor] Public endpoint failure'
run_url="${GITHUB_SERVER_URL:-https://github.com}/${GITHUB_REPOSITORY}/actions/runs/${GITHUB_RUN_ID:-unknown}"
paths=('/' '/up' '/api/v1/site' '/manage/login')
problems=()

if [[ -z "${GH_TOKEN:-}" || -z "${GITHUB_REPOSITORY:-}" ]]; then
  echo 'GitHub repository token and context required for incident reporting' >&2
  exit 2
fi

for path in "${paths[@]}"; do
  code='000'
  for attempt in 1 2; do
    # Ignore response body; avoid accidentally capturing customer or server internals.
    code="$(curl --silent --show-error --location --max-redirs 2 --connect-timeout 6 --max-time 15 \
      --output /dev/null --write-out '%{http_code}' "${origin}${path}" 2>/dev/null || true)"
    [[ "$code" == '200' ]] && break
    [[ "$attempt" -lt 2 ]] && sleep 3
  done
  printf 'GET %s -> %s\n' "$path" "$code"
  [[ "$code" == '200' ]] || problems+=("${path}: HTTP ${code:-000}")
done

# Restrict incident deduplication to this exact sentinel title; avoid issue spam.
existing="$(gh issue list --repo "$GITHUB_REPOSITORY" --state open --limit 100 \
  --json number,title --jq '.[] | select(.title == "[Carfolk Monitor] Public endpoint failure") | .number' | head -n1)"

if (( ${#problems[@]} > 0 )); then
  printf 'Carfolk public-health failures detected:\n%s\n' "${problems[*]}" >&2
  if [[ -z "$existing" ]]; then
    {
      printf '## Automated public endpoint incident\n\n'
      printf 'A check reported one or more unavailable endpoints. **No customer data was collected.**\n\n'
      for item in "${problems[@]}"; do printf -- '- %s\n' "$item"; done
      printf '\nWorkflow evidence: %s\n' "$run_url"
      printf '\nReview HostPinnacle availability and the private Laravel/server logs. This monitor cannot detect failed authenticated uploads or PHP 413 errors.\n'
    } > "$RUNNER_TEMP/carfolk-incident.md"
    gh issue create --repo "$GITHUB_REPOSITORY" --title "$issue_title" \
      --body-file "$RUNNER_TEMP/carfolk-incident.md"
  else
    printf 'Existing incident #%s remains open.\n' "$existing"
  fi
  exit 1
fi

if [[ -n "$existing" ]]; then
  gh issue comment "$existing" --repo "$GITHUB_REPOSITORY" \
    --body "All monitored public endpoints returned HTTP 200. Recovery check: $run_url"
  gh issue close "$existing" --repo "$GITHUB_REPOSITORY"
fi

echo 'Carfolk public endpoints returned HTTP 200.'
