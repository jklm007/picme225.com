#!/bin/bash
# ==============================================================================
# Script K3s WORKER (ORACLE ARM / MICRO)
# Ce script prépare les noeuds workers Oracle pour rejoindre le cluster GCP
# ==============================================================================

if [ -z "$1" ] || [ -z "$2" ] || [ -z "$3" ]; then
    echo "Usage: ./setup_k8s_worker.sh <K3S_URL> <K3S_TOKEN> <NODE_TYPE>"
    echo "Exemple: ./setup_k8s_worker.sh https://100.x.x.x:6443 K10xxxxx oracle-arm"
    echo "NODE_TYPE peut être: oracle-arm ou oracle-micro"
    exit 1
fi

K3S_URL=$1
K3S_TOKEN=$2
NODE_TYPE=$3

echo "🚀 Installation de Tailscale (VPN Multi-Cloud)..."
curl -fsSL https://tailscale.com/install.sh | sh
sudo tailscale up

TAILSCALE_IP=$(tailscale ip -4)

echo "🚀 Installation de K3s Worker et jonction au Master..."
curl -sfL https://get.k3s.io | K3S_URL=${K3S_URL} K3S_TOKEN=${K3S_TOKEN} INSTALL_K3S_EXEC="agent --node-ip=${TAILSCALE_IP} --flannel-iface=tailscale0 --node-label node-role.kubernetes.io/${NODE_TYPE}=true" sh -

echo "✅ K3s Worker installé avec succès !"
echo "Ce serveur Oracle a rejoint le cluster K3s du Master (GCP) sous le label: ${NODE_TYPE}"
