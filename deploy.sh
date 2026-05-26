#!/bin/bash
SFTP_USER="2641ac26-5532-11f1-b5c6-00163e94b645"
SFTP_HOST="sftp.sd3.gpaas.net"
REMOTE_BASE="vhosts/vitsmanage.com"

echo "Déploiement VIT-S sur Gandi..."

sftp $SFTP_USER@$SFTP_HOST << EOF
# Créer les dossiers
mkdir $REMOTE_BASE/vits
mkdir $REMOTE_BASE/vits/app
mkdir $REMOTE_BASE/vits/bootstrap
mkdir $REMOTE_BASE/vits/config
mkdir $REMOTE_BASE/vits/database
mkdir $REMOTE_BASE/vits/resources
mkdir $REMOTE_BASE/vits/routes
mkdir $REMOTE_BASE/vits/storage
mkdir $REMOTE_BASE/vits/vendor
exit
EOF

echo "Structure créée !"
