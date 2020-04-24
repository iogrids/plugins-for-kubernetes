```
kubectl apply --validate=false -f https://raw.githubusercontent.com/jetstack/cert-manager/release-0.13/deploy/manifests/00-crds.yaml
helm repo add jetstack https://charts.jetstack.io
helm install --name cert-manager --namespace cert-manager jetstack/cert-manager
```

# Install the certificate (Wait for some time till the cert-manager pods are up and running)

```
kubectl apply -f production-cluster-issuer.yaml
```

# To check whether cluster issuer is setup correctly

```
kubectl get clusterissuer
```