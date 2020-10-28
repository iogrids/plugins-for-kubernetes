# Install cert-manager

```
1. kubectl apply -f cert-manager.yaml
```

# To check for cert-manager pods

```
kubectl get pods --namespace cert-manager
```

# Install the certificate (Wait for some time till the cert-manager pods are up and running)

```
kubectl apply -f production_issuer.yaml
```
