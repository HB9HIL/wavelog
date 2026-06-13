#!/usr/bin/env bash
#
# Append new Weblate translators to the README translator list.
#
# Queries the Weblate "credits" API for the wavelog project, collects every
# translator (username + full name), and appends those not yet present in the
# README to the block delimited by the <!-- TRANSLATORS:START/END --> markers.
#
# Append-only and idempotent: existing entries (incl. manual non-Weblate links)
# are never touched, already-listed usernames are skipped.
#
# Requires: curl, jq, and a Weblate API token in $WEBLATE_API_TOKEN.

set -euo pipefail

README="${README_PATH:-README.md}"
BASE="https://translate.wavelog.org"
CREDITS_URL="$BASE/api/projects/wavelog/credits/?start=2020-01-01&end=2099-01-01"
MIN_CHANGES="${MIN_CHANGES:-1}"

# Usernames to never add (bot accounts are filtered via regex below; add
# maintainers here if they should be kept out of the translator list).
EXCLUDE_USERS=(HB9HIL int2001 DF2ET)

: "${WEBLATE_API_TOKEN:?WEBLATE_API_TOKEN is required}"

# 1. Fetch credits (fail hard on HTTP errors).
json="$(curl -sf -H "Authorization: Token $WEBLATE_API_TOKEN" "$CREDITS_URL")"

# 2. Flatten across all languages -> "username<TAB>full_name", drop bot
#    accounts and entries below the change threshold, unique by username.
candidates="$(
	printf '%s' "$json" | jq -r --argjson min "$MIN_CHANGES" '
		.[] | to_entries[] | .value[]
		| select(.change_count >= $min)
		| select(.username | test("^addon:|^anonymous$") | not)
		| [.username, (.full_name // "")] | @tsv' \
	| sort -t"$(printf '\t')" -k1,1 -u
)"

# 3. Usernames already present in the README (lowercased for matching).
existing="$(grep -oE 'user/[^/)]+/' "$README" \
	| sed 's#^user/##; s#/$##' \
	| tr '[:upper:]' '[:lower:]' \
	| sort -u)"

# 4. Build the markdown for translators not yet listed.
TAB="$(printf '\t')"
new_entries=""
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

	grep -qxF "$lc" <<< "$existing" && continue   # already listed

	display="${fullname:-$username}"
	new_entries+=", [${display}](${BASE}/user/${username}/)"
	echo "Adding translator: ${username} (${display})" >&2
done <<< "$candidates"

if [[ -z "$new_entries" ]]; then
	echo "No new translators." >&2
	exit 0
fi

# 5. Append the new entries to the line directly preceding the END marker.
tmp="$(mktemp)"
awk -v add="$new_entries" '
	function flush() { if (havePrev) { print prev; havePrev=0 } }
	/<!-- TRANSLATORS:END -->/ {
		if (havePrev) { print prev add; havePrev=0 }
		print $0; next
	}
	{ flush(); prev=$0; havePrev=1 }
	END { flush() }
' "$README" > "$tmp"
mv "$tmp" "$README"

echo "README updated." >&2
