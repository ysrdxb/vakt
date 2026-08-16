#!/usr/bin/env bash
#
# Deploy Vakt to health.kunnatta.is (1984.is shared hosting, SFTP-only).
#
# The server has no shell access and open_basedir restricts PHP to the web
# root, so the whole app (including vendor/) lives inside /health/htdocs.
# deploy/index.php and deploy/htaccess adapt Laravel to that layout.
#
# Credentials are read from .env.deploy (not committed):
#   DEPLOY_HOST, DEPLOY_PORT, DEPLOY_USER, DEPLOY_PASS, DEPLOY_PATH
#
# Requires locally: php 8.3+, composer, node, rsync, lftp
#
set -euo pipefail
cd "$(dirname "$0")"

source .env.deploy

echo "==> Building"
composer install --no-dev --optimize-autoloader --no-interaction --quiet
npm ci --silent
npm run build --silent

echo "==> Staging"
STAGE=.deploy-stage
rm -rf "$STAGE"
mkdir -p "$STAGE"
rsync -a \
  --exclude public --exclude node_modules --exclude .git --exclude tests \
  --exclude .env --exclude .env.example --exclude .env.deploy \
  --exclude database/database.sqlite \
  --exclude deploy.sh --exclude deploy --exclude .deploy-stage \
  ./ "$STAGE/"
rsync -a public/ "$STAGE/"
cp deploy/index.php "$STAGE/index.php"
cp deploy/htaccess "$STAGE/.htaccess"

echo "==> Uploading (only changed files)"
# .env and the SQLite database on the server are never touched.
lftp -p "$DEPLOY_PORT" -u "$DEPLOY_USER,$DEPLOY_PASS" "sftp://$DEPLOY_HOST" -e "
set sftp:auto-confirm yes;
mirror -R --parallel=4 --only-newer --no-perms \
  --exclude-glob .env \
  --exclude-glob database/database.sqlite \
  --exclude-glob storage/framework/cache/* \
  --exclude-glob storage/framework/sessions/* \
  --exclude-glob storage/framework/views/* \
  --exclude-glob storage/logs/* \
  $STAGE/ $DEPLOY_PATH;
bye"

echo "==> Clearing caches"
curl -fsS https://health.kunnatta.is/clear-cache || true
echo
echo "Done. ATH: ef deploy/htaccess breyttist tekur breytingin ekki gildi"
echo "fyrr en PHP er endurræst handvirkt í 1984-stjórnborðinu."
