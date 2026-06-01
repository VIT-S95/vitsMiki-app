#!/bin/bash
set -e

# ─── Paramètre ───────────────────────────────────────────────────────────────
if [ -z "$1" ]; then
    echo "Usage : ./deploy.sh \"message de commit\""
    exit 1
fi

MSG="$1"

# ─── 1. Commit ───────────────────────────────────────────────────────────────
echo ""
echo "▶ [1/2] Commit..."
git add .
git commit -m "$MSG" || echo "   (rien à committer, on continue)"

# ─── 2. Push GitHub ──────────────────────────────────────────────────────────
echo ""
echo "▶ [2/2] Push GitHub (origin production)..."
git push origin production
echo "   ✓ GitHub OK"

# ─── Résumé ──────────────────────────────────────────────────────────────────
echo ""
echo "✅ Push terminé : \"$MSG\""
echo ""
echo "👉 Maintenant dans le Terminal cPanel o2switch, lance :"
echo ""
echo "   cd ~/public_html/vits/vits && git pull origin production && php artisan config:clear && php artisan cache:clear && php artisan view:clear"
