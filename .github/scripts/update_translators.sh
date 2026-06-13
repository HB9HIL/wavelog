#!/usr/bin/env bash
#
# Regenerate the README translator list from Weblate.
#
# Queries the Weblate "credits" API for the wavelog project, collects every
# translator (username + full name), and rebuilds the block delimited by the
# <!-- TRANSLATORS:START/END --> markers from scratch: deduplicated by username,
# sorted by username (case-insensitive), bot/maintainer accounts removed.
#
# Idempotent: an unchanged list produces no diff, so the workflow only commits
# when translators were actually added or removed on Weblate.
#
# Requires: curl, jq, and a Weblate API token in $WEBLATE_API_TOKEN.

set -euo pipefail

README="${README_PATH:-README.md}"
BASE="https://translate.wavelog.org"
CREDITS_URL="$BASE/api/projects/wavelog/credits/?start=2020-01-01&end=2099-01-01"
MIN_CHANGES="${MIN_CHANGES:-1}"

# Usernames to never list (bot accounts are filtered via regex below; these are
# core maintainers credited as translators but kept out of the list).
EXCLUDE_USERS=(HB9HIL int2001 DF2ET)

: "${WEBLATE_API_TOKEN:?WEBLATE_API_TOKEN is required}"

# 1. Fetch credits (fail hard on HTTP errors).
json="$(curl -sf -H "Authorization: Token $WEBLATE_API_TOKEN" "$CREDITS_URL")"

# 2. Flatten across all languages -> "username<TAB>full_name", drop bot accounts
#    and entries below the change threshold, unique by username, sorted by
#    username case-insensitively.
rows="$(
	printf '%s' "$json" | jq -r --argjson min "$MIN_CHANGES" '
		.[] | to_entries[] | .value[]
		| select(.change_count >= $min)
		| select(.username | test("^addon:|^anonymous$") | not)
		| [.username, (.full_name // "")] | @tsv' \
	| sort -f -t"$(printf '\t')" -k1,1 -u
)"

# 3. Build the comma-separated markdown line, skipping excluded maintainers.
TAB="$(printf '\t')"
list=""
while IFS= read -r line; do
	[[ -z "$line" ]] && continue
	username="${line%%"$TAB"*}"
	fullname="${line#*"$TAB"}"
	[[ "$fullname" == "$line" ]] && fullname=""   # guard: no tab present
	lc="$(printf '%s' "$username" | tr '[:upper:]' '[:lower:]')"

	skip=0
	if [[ ${#EXCLUDE_USERS[@]} -gt 0 ]]; then
		for ex in "${EXCLUDE_USERS[@]}"; do
			[[ "$lc" == "$(printf '%s' "$ex" | tr '[:upper:]' '[:lower:]')" ]] && skip=1
		done
	fi
	[[ $skip -eq 1 ]] && continue

	display="${fullname:-$username}"
	if [[ -z "$list" ]]; then
		list="[${display}](${BASE}/user/${username}/)"
	else
		list="${list}, [${display}](${BASE}/user/${username}/)"
	fi
done <<< "$rows"

if [[ -z "$list" ]]; then
	echo "Credits API returned no translators - aborting to avoid wiping the list." >&2
	exit 1
fi

# 4. Replace the content between the markers with the freshly built list.
tmp="$(mktemp)"
LISTLINE="$list" awk '
	/<!-- TRANSLATORS:START -->/ { print; print ENVIRON["LISTLINE"]; skip=1; next }
	/<!-- TRANSLATORS:END -->/   { skip=0; print; next }
	skip { next }
	{ print }
' "$README" > "$tmp"
mv "$tmp" "$README"

echo "Translator list regenerated." >&2
