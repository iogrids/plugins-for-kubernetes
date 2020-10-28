# Rabbitmq Setup

## 1. Create Secret Cookie
kubectl create secret generic rabbitmq-config --from-literal=erlang-cookie=rabbitmq-k8s-Dem0

## 2. Apply the StatefulSet
kubectl apply -f rabbitmq-statefulset.yaml

## 3. To delete rabbitmq
kubectl delete statefulset rabbitmq
kubectl delete svc rabbitmq rabbitmq-management
kubectl delete secrets rabbitmq-config
kubectl delete pvc -l app=rabbitmq
