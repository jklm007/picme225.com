#!/bin/bash
# ==============================================================================
# Script K3s MASTER (GCP)
# Ce script prépare le noeud principal Kubernetes sur Google Cloud
# ==============================================================================

echo "🚀 Installation de Tailscale (VPN Multi-Cloud)..."
curl -fsSL https://tailscale.com/install.sh | sh
sudo tailscale up

echo "✅ Tailscale installé. Veuillez noter l'adresse IP Tailscale de ce serveur (ex: 100.x.x.x)."
echo "Cette adresse sera utilisée par les workers Oracle pour rejoindre le cluster."
TAILSCALE_IP=$(tailscale ip -4)

echo "🚀 Installation de K3s Master (Kubernetes Léger)..."
# On force K3s à écouter sur l'IP Tailscale pour la sécurité
curl -sfL https://get.k3s.io | INSTALL_K3S_EXEC="server --node-ip=${TAILSCALE_IP} --flannel-iface=tailscale0 --advertise-address=${TAILSCALE_IP}" sh -

echo "✅ K3s Master installé."

# Récupérer le token pour les workers
NODE_TOKEN=$(sudo cat /var/lib/rancher/k3s/server/node-token)

echo "======================================================================"
echo "⚠️  IMPORTANT : SAUVEGARDEZ CES INFORMATIONS POUR LES WORKERS ORACLE  ⚠️"
echo "======================================================================"
echo "K3S_URL=https://${TAILSCALE_IP}:6443"
echo "K3S_TOKEN=${NODE_TOKEN}"
echo "======================================================================"
