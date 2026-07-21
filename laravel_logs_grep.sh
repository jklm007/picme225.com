#!/bin/bash
POD=$(sudo k3s kubectl get pods -l app=laravel -o jsonpath='{.items[0].metadata.name}')
sudo k3s kubectl logs "$POD" --tail=300 | grep -B 10 -A 2 "QueryException" || sudo k3s kubectl logs "$POD" --tail=100
